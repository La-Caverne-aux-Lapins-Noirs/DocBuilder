<?php

/**
 * Execute a command with optional stdin, capture stdout/stderr, return [code, out, err].
 * Uses proc_open without invoking a shell.
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
        return [127, "", "proc_open failed"];

    if ($stdin !== null) {
        fwrite($pipes[0], $stdin);
    }
    fclose($pipes[0]);

    $out = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    $err = stream_get_contents($pipes[2]);
    fclose($pipes[2]);

    $code = proc_close($proc);

    if ($debug)
    {
        // Keep it readable: show cmd as joined string (still no shell)
        echo "CMD: ".implode(" ", array_map(fn($x)=>strval($x), $cmd))."\n";
        if ($out !== "") echo "STDOUT:\n".$out."\n";
        if ($err !== "") echo "STDERR:\n".$err."\n";
    }

    return ([$code, $out, $err]);
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

    // 1) pandoc: stdin -> output.tex
    $pandocCmd =
	[
            "pandoc",
            "/dev/stdin",
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
    array_push($pandocCmd,
        "--columns", "150",
        "-V", "papersize=a4",
        "-f", "markdown",
        "-t", "latex"
    );
    if ($debug)
        $pandocCmd[] = "--verbose";

    [$code1, $out1, $err1] = _run_cmd($pandocCmd, $str, $debug);
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

