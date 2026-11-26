<?php

/**
 * Execute a command with optional stdin, capture stdout/stderr, return
 * [code, out, err].  stdout and stderr must be drained concurrently: reading
 * one stream to EOF before touching the other can deadlock as soon as the
 * child fills the other pipe.
 */
function _run_cmd(array $cmd, ?string $stdin = null, bool $debug = false): array
{
    $descriptors =
        [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"],
        ];

    $proc = proc_open($cmd, $descriptors, $pipes, null, null, ["bypass_shell" => true]);
    if (!is_resource($proc))
        return ([127, "", "proc_open failed"]);

    stream_set_blocking($pipes[0], false);
    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);

    $input = $stdin ?? "";
    $input_offset = 0;
    $stdin_open = true;
    $stdout_open = true;
    $stderr_open = true;
    $out = "";
    $err = "";
    $known_exit_code = NULL;

    if ($input === "")
    {
        fclose($pipes[0]);
        $stdin_open = false;
    }

    while ($stdin_open || $stdout_open || $stderr_open)
    {
        $read = [];
        if ($stdout_open)
            $read[] = $pipes[1];
        if ($stderr_open)
            $read[] = $pipes[2];
        $write = [];
        if ($stdin_open)
            $write[] = $pipes[0];
        $except = NULL;

        $ready = @stream_select($read, $write, $except, 1, 0);
        if ($ready === false)
        {
            $read = [];
            $write = [];
        }

        foreach ($write as $stream)
        {
            $chunk = substr($input, $input_offset, 16384);
            $written = @fwrite($stream, $chunk);
            if ($written === false)
            {
                fclose($pipes[0]);
                $stdin_open = false;
                break;
            }
            $input_offset += $written;
            if ($input_offset >= strlen($input))
            {
                fclose($pipes[0]);
                $stdin_open = false;
            }
        }

        foreach ($read as $stream)
        {
            $chunk = @fread($stream, 16384);
            if ($chunk !== false && $chunk !== "")
            {
                if ($stream === $pipes[1])
                    $out .= $chunk;
                else
                    $err .= $chunk;
            }
            if (feof($stream))
            {
                if ($stream === $pipes[1])
                {
                    fclose($pipes[1]);
                    $stdout_open = false;
                }
                else
                {
                    fclose($pipes[2]);
                    $stderr_open = false;
                }
            }
        }

        $status = proc_get_status($proc);
        if (!$status["running"] && $status["exitcode"] >= 0)
            $known_exit_code = $status["exitcode"];

        if (!$status["running"] && $ready == 0)
        {
            if ($stdin_open)
            {
                fclose($pipes[0]);
                $stdin_open = false;
            }
            foreach ([1, 2] as $index)
            {
                $open = $index == 1 ? $stdout_open : $stderr_open;
                if ($open)
                {
                    $chunk = stream_get_contents($pipes[$index]);
                    if ($chunk !== false && $chunk !== "")
                    {
                        if ($index == 1)
                            $out .= $chunk;
                        else
                            $err .= $chunk;
                    }
                    fclose($pipes[$index]);
                    if ($index == 1)
                        $stdout_open = false;
                    else
                        $stderr_open = false;
                }
            }
        }
    }

    $code = proc_close($proc);
    if ($code == -1 && $known_exit_code !== NULL)
        $code = $known_exit_code;

    if ($debug)
    {
        echo "CMD: ".implode(" ", array_map(fn($x)=>strval($x), $cmd))."\n";
        if ($out !== "") echo "STDOUT:\\n".$out."\n";
        if ($err !== "") echo "STDERR:\\n".$err."\n";
    }

    return ([$code, $out, $err]);
}

/**
 * Pandoc's yaml_metadata_block extension accepts metadata blocks anywhere in
 * the Markdown stream.  Exercise text may legitimately contain a horizontal
 * rule (`---`) followed by lines containing colons; Pandoc can then mistake
 * ordinary subject text for YAML and abort with code 64.
 *
 * Keep only the first document front matter as metadata.  It is supplied to
 * Pandoc through --metadata-file while YAML metadata parsing is disabled for
 * the document body.
 */
function _extract_pandoc_metadata(string $document): array
{
    $document = str_replace(["\r\n", "\r"], "\n", $document);
    $lines = explode("\n", $document);
    $count = count($lines);
    $start = 0;

    while ($start < $count && trim($lines[$start]) === "")
        ++$start;
    if ($start >= $count || trim($lines[$start]) !== "---")
        return ([NULL, $document]);

    $end = $start + 1;
    while ($end < $count)
    {
        $marker = trim($lines[$end]);
        if ($marker === "---" || $marker === "...")
            break;
        ++$end;
    }
    if ($end >= $count)
        throw new RuntimeException("Unterminated Pandoc YAML metadata block.");

    $metadata = implode("\n", array_slice($lines, $start + 1, $end - $start - 1))."\n";
    $body = array_merge(array_slice($lines, 0, $start), array_slice($lines, $end + 1));
    return ([$metadata, implode("\n", $body)]);
}

/**
 * Ensure a directory exists.
 */
function _mkdir_p(string $dir): void
{
    if ($dir === "" || $dir === "." || $dir === "/")
	return ;
    if (is_dir($dir))
	return ;
    if (!mkdir($dir, 0755, true) && !is_dir($dir))
        throw new RuntimeException("Cannot create directory: ".$dir);
}

/**
 * Remove a temporary directory recursively without invoking a shell.
 */
function _remove_tree(string $path): void
{
    if ($path === "" || $path === "." || $path === "/" || !file_exists($path))
	return ;
    if (is_file($path) || is_link($path))
    {
	@unlink($path);
	return ;
    }
    $items = scandir($path);
    if ($items === false)
	return ;
    foreach ($items as $item)
    {
        if ($item === "." || $item === "..")
	    continue ;
        _remove_tree($path."/".$item);
    }
    @rmdir($path);
}

function Compile($conf, $str)
{
    $debug = !empty($conf[".Debug"]);

    if (empty($conf[".Directory"]))
        throw new RuntimeException("Missing .Directory in configuration.");

    $headerTex = rtrim($conf[".Directory"], "/")."/configuration.tex";
    if (!is_file($headerTex))
        throw new RuntimeException("Missing configuration.tex for document: ".$headerTex);
    $staticExtraHeaderTex = rtrim($conf[".Directory"], "/")."/extra-header.tex";
    $runtimeExtraHeader = isset($conf[".LatexExtraHeader"]) && is_string($conf[".LatexExtraHeader"])
        ? trim($conf[".LatexExtraHeader"])
        : "";

    $outFile = $conf[".OutputFile"] ?? "a.out.pdf";
    if (!preg_match('/\.pdf$/i', $outFile))
        $outFile .= ".pdf";

    $finalDir = dirname($outFile);
    $baseName = pathinfo($outFile, PATHINFO_FILENAME);
    _mkdir_p($finalDir);

    // Never expose the requested output basename to TeX as its jobname.
    // A perfectly valid caller-side temporary file may begin with a dot
    // (for example .bulletin_202509_xxx.pdf), but TeX configured with
    // openout_any=p refuses to create the corresponding hidden .aux/.log
    // files.  The workspace is already unique, so a fixed internal jobname
    // is both safe and sufficient; the generated PDF is copied to the exact
    // requested output path afterwards.
    $jobName = "document";

    // Temp workspace
    $tmpBase = rtrim(sys_get_temp_dir(), "/");
    $tmpDir = $tmpBase."/docbuilder-".bin2hex(random_bytes(8));
    _mkdir_p($tmpDir);

    $texPath = $tmpDir."/output.tex";
    $pdfPath = $tmpDir."/".$jobName.".pdf";
    $markdownPath = $tmpDir."/input.md";
    $metadataPath = $tmpDir."/metadata.yaml";

    // Renderers may provide Pandoc variables directly.  In particular, the
    // subject renderer does this so exercise Markdown is never interpreted as
    // YAML metadata.  Legacy document renderers can keep their initial YAML
    // front matter for now.
    $pandocVariables = isset($conf[".PandocVariables"]) && is_array($conf[".PandocVariables"])
        ? $conf[".PandocVariables"]
        : [];
    if ($pandocVariables !== [])
    {
        $metadata = NULL;
        $body = $str;
    }
    else
        [$metadata, $body] = _extract_pandoc_metadata($str);

    if (file_put_contents($markdownPath, $body) === false)
        throw new RuntimeException("Cannot create Pandoc input: ".$markdownPath);
    if ($metadata !== NULL && file_put_contents($metadataPath, $metadata) === false)
        throw new RuntimeException("Cannot create Pandoc metadata: ".$metadataPath);

    // A renderer may declare packages/preamble fragments dynamically while
    // BuildDocument() is running.  Persist that fragment in the workspace so
    // Pandoc can include it exactly like configuration.tex.  This is more
    // reliable than depending on an optional sidecar file being packaged.
    $runtimeExtraHeaderTex = NULL;
    if ($runtimeExtraHeader !== "")
    {
        $runtimeExtraHeaderTex = $tmpDir."/renderer-header.tex";
        if (file_put_contents($runtimeExtraHeaderTex, $runtimeExtraHeader."\n") === false)
        {
            if (!$debug)
                _remove_tree($tmpDir);
            throw new RuntimeException("Cannot create renderer LaTeX header: ".$runtimeExtraHeaderTex);
        }
    }

    // 1) pandoc: Markdown file + isolated metadata -> output.tex
    $pandocCmd =
	[
            "pandoc",
            $markdownPath,
            "-o", $texPath,
            "--pdf-engine=xelatex",
            "--include-in-header", $headerTex,
	];
    // Renderers may need document-specific preamble additions without
    // replacing their existing configuration.tex. Prefer runtime declarations
    // because they cannot disappear through an incomplete install; retain the
    // historical sidecar as a fallback for renderers that still use it.
    if ($runtimeExtraHeaderTex !== NULL)
    {
        $pandocCmd[] = "--include-in-header";
        $pandocCmd[] = $runtimeExtraHeaderTex;
    }
    else if (is_file($staticExtraHeaderTex))
    {
        $pandocCmd[] = "--include-in-header";
        $pandocCmd[] = $staticExtraHeaderTex;
    }
    if ($metadata !== NULL)
    {
        $pandocCmd[] = "--metadata-file";
        $pandocCmd[] = $metadataPath;
    }
    foreach ($pandocVariables as $key => $value)
    {
        if (!is_scalar($value))
            throw new RuntimeException("Invalid Pandoc variable '$key': scalar value expected.");
        $pandocCmd[] = "-V";
        $pandocCmd[] = (string)$key."=".(string)$value;
    }
    array_push($pandocCmd,
        "--columns", "150",
        "-V", "papersize=a4",
        "-f", "markdown-yaml_metadata_block",
        "-t", "latex"
    );
    if ($debug)
        $pandocCmd[] = "--verbose";

    [$code1, $out1, $err1] = _run_cmd($pandocCmd, null, $debug);
    if ($code1 !== 0)
    {
        // Keep temp dir for debug? If debug, keep it. Otherwise cleanup.
        if (!$debug)
            _remove_tree($tmpDir);
        throw new RuntimeException("pandoc failed (code=$code1):\n".$err1);
    }

    // 2) latexmk: output.tex -> PDF in temp dir (no shell concat)
    $latexmkCmd =
	[
            "latexmk",
            "-xelatex",
            "--shell-escape",
            "-jobname=".$jobName,
            "-output-directory=".$tmpDir,
            $texPath,
	];

    [$code2, $out2, $err2] = _run_cmd($latexmkCmd, null, $debug);
    if ($code2 !== 0 || !is_file($pdfPath))
    {
        if (!$debug)
            _remove_tree($tmpDir);
        $details = trim($out2."\n".$err2);
        throw new RuntimeException("latexmk failed (code=$code2).".($details !== "" ? "\n".$details : ""));
    }

    // Copy result
    $finalPdf = rtrim($finalDir, "/")."/".$baseName.".pdf";
    if (!copy($pdfPath, $finalPdf))
    {
        if (!$debug)
            _remove_tree($tmpDir);
        throw new RuntimeException("Cannot copy PDF to: ".$finalPdf);
    }

    if ($debug)
    {
        echo "PDF generated: ".$finalPdf."\n";
        echo "Temp dir kept: ".$tmpDir."\n";
    }
    else
        _remove_tree($tmpDir);
}

