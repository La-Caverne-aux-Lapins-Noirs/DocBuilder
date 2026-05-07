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

    $outFile = $conf[".OutputFile"] ?? "a.out.pdf";
    if (!preg_match('/\.pdf$/i', $outFile))
        $outFile .= ".pdf";

    $finalDir = dirname($outFile);
    $baseName = pathinfo($outFile, PATHINFO_FILENAME);
    _mkdir_p($finalDir);

    // Temp workspace
    $tmpBase = rtrim(sys_get_temp_dir(), "/");
    $tmpDir = $tmpBase."/docbuilder-".bin2hex(random_bytes(8));
    _mkdir_p($tmpDir);

    $texPath = $tmpDir."/output.tex";
    $pdfPath = $tmpDir."/".$baseName.".pdf";

    // 1) pandoc: stdin -> output.tex
    $pandocCmd =
	[
            "pandoc",
            "/dev/stdin",
            "-o", $texPath,
            "--pdf-engine=xelatex",
            "--include-in-header", $headerTex,
            "--columns", "150",
            "-V", "papersize=a4",
            "-f", "markdown",
            "-t", "latex",
	];
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
            "--shell-escape",
            "-jobname=".$baseName,
            "-output-directory=".$tmpDir,
            $texPath,
	];

    [$code2, $out2, $err2] = _run_cmd($latexmkCmd, null, $debug);
    if ($code2 !== 0 || !is_file($pdfPath))
    {
        if (!$debug)
            _remove_tree($tmpDir);
        throw new RuntimeException("latexmk failed (code=$code2).\n".$err2);
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

