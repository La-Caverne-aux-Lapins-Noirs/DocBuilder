<?php

function RunMergeconf(array $args, $dabsic_hash = NULL): string
{
    $cmd = array_merge(["mergeconf"], $args);
    if ($dabsic_hash !== NULL)
        $cmd = array_merge($cmd, ["-m", "DocBuilder.DabsicHash=".(string)$dabsic_hash]);
    $cmd = array_merge($cmd, ["-of", ".json", "--resolve"]);
    $descriptors = [0 => ["pipe", "r"], 1 => ["pipe", "w"], 2 => ["pipe", "w"]];
    $proc = proc_open($cmd, $descriptors, $pipes, null, null, ["bypass_shell" => true]);
    if (!is_resource($proc))
        throw new RuntimeException("Cannot start mergeconf.");

    fclose($pipes[0]);
    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);

    $json = "";
    $err = "";
    $err_limit = 8 * 1024 * 1024;
    $err_truncated = false;
    $open = [1 => true, 2 => true];
    $deadline = microtime(true) + 300;
    $known_exit_code = NULL;

    while ($open[1] || $open[2])
    {
        $read = [];
        if ($open[1])
            $read[] = $pipes[1];
        if ($open[2])
            $read[] = $pipes[2];

        $write = NULL;
        $except = NULL;
        $ready = count($read)
            ? @stream_select($read, $write, $except, 0, 200000) : 0;
        if ($ready === false)
            $read = [];

        foreach ($read as $stream)
        {
            $index = $stream === $pipes[1] ? 1 : 2;
            $chunk = fread($stream, 16384);
            if ($chunk !== false && $chunk !== "")
            {
                if ($index == 1)
                    $json .= $chunk;
                else if (!$err_truncated)
                {
                    $remaining = $err_limit - strlen($err);
                    if ($remaining > 0)
                        $err .= substr($chunk, 0, $remaining);
                    if (strlen($chunk) > $remaining)
                        $err_truncated = true;
                }
            }
            if (feof($stream))
            {
                fclose($stream);
                $open[$index] = false;
            }
        }

        $status = proc_get_status($proc);
        if (!$status["running"] && $status["exitcode"] >= 0)
            $known_exit_code = $status["exitcode"];

        if ($status["running"] && microtime(true) >= $deadline)
        {
            proc_terminate($proc);
            usleep(100000);
            $status = proc_get_status($proc);
            if ($status["running"])
                proc_terminate($proc, 9);
            foreach ([1, 2] as $index)
                if ($open[$index])
                {
                    fclose($pipes[$index]);
                    $open[$index] = false;
                }
            proc_close($proc);
            throw new RuntimeException("mergeconf timed out after 300 seconds.");
        }

        if (!$status["running"] && !count($read))
            foreach ([1, 2] as $index)
                if ($open[$index] && feof($pipes[$index]))
                {
                    fclose($pipes[$index]);
                    $open[$index] = false;
                }
    }

    $code = proc_close($proc);
    if ($code == -1 && $known_exit_code !== NULL)
        $code = $known_exit_code;
    if ($err_truncated)
        $err .= "\n[stderr truncated]\n";
    if ($code !== 0)
        throw new RuntimeException("mergeconf failed with code $code:\n".$err);
    return ($json);
}

function BuildConfiguration($argc, $argv)
{
    $Cli = ["--resolve"];
    $Blank = false;
    $HashOnly = false;
    $MetadataOnly = false;
    $HashFile = NULL;
    $Output = "a.out.pdf";
    $Debug = false;
    for ($i = 1; $i < $argc; ++$i)
    {
        if ($argv[$i] == "-d")
            $Debug = true;
        else if ($argv[$i] == "--blank")
            $Blank = true;
        else if ($argv[$i] == "--hash-only")
            $HashOnly = true;
        else if ($argv[$i] == "--metadata-only")
            $MetadataOnly = true;
        else if ($argv[$i] == "--hash-file")
        {
            if ($i + 1 == $argc)
            {
                echo "Missing file name after the --hash-file option.\n";
                exit (1);
            }
            $HashFile = $argv[++$i];
        }
	else if ($argv[$i] == "-o")
	{
            if ($i + 1 == $argc)
	    {
                echo "Missing file name after the -o option.\n";
                exit (1);
            }
            $Output = $argv[++$i];
        }
	else
            $Cli[] = $argv[$i];
    }

    // First pass: reserve the hash variable as an empty value so Dabsic may
    // reference it while mergeconf resolves the complete input tree. The digest
    // is computed from that fully resolved configuration with the reserved
    // variable removed entirely, so it can never depend on itself.
    $seed_json = RunMergeconf($Cli, "");
    if (trim($seed_json) === "")
    {
        echo "mergeconf produced no output.\n";
        exit (1);
    }
    $SeedConfiguration = json_decode($seed_json, true);
    if (!is_array($SeedConfiguration))
    {
        echo "Invalid JSON from mergeconf.\n";
        echo "Raw output:\n".$seed_json."\n";
        exit (1);
    }

    $DabsicHash = DocBuilderConfigurationHash($SeedConfiguration);

    // Second pass: expose the fingerprint to Dabsic itself. Appending our -m
    // after every user argument makes DocBuilder.DabsicHash a reserved value
    // that cannot be forged from the command line or an input file.
    $json = RunMergeconf($Cli, $DabsicHash);
    if (trim($json) === "")
    {
        echo "mergeconf produced no output.\n";
        exit (1);
    }
    $Configuration = json_decode($json, true);
    if (!is_array($Configuration))
    {
        echo "Invalid JSON from mergeconf.\n";
        echo "Raw output:\n".$json."\n";
        exit (1);
    }
    DocBuilderInjectHash($Configuration, $DabsicHash);

    // mergeconf remains the single parser/resolver for all Dabsic input.
    // Once the complete tree exists, normalize generic signatory declarations
    // into the Signatories.<Role> scopes consumed by document renderers.
    ResolveSignatories($Configuration, $Blank);

    // Only publish the sidecar once every DocBuilder-level validation has
    // succeeded. This prevents callers from treating a rejected document as
    // having a valid authoritative fingerprint.
    if ($HashFile !== NULL && @file_put_contents($HashFile, $DabsicHash."\n") === false)
        throw new RuntimeException("Cannot write DocBuilder hash file '$HashFile'.");

    $includePaths = [];
    for ($i = 0; $i < count($Cli); ++$i)
    {
        if (($Cli[$i] == "-I" || $Cli[$i] == "-i") && $i + 1 < count($Cli))
        {
            $path = $Cli[++$i];
            if ($Cli[$i - 1] == "-i")
                $path = dirname($path);
            $resolved = realpath($path);
            if ($resolved !== false && is_dir($resolved))
                $includePaths[] = rtrim($resolved, "/");
        }
    }
    $cwd = getcwd();
    if ($cwd !== false)
        $includePaths[] = rtrim($cwd, "/");

    $Configuration[".Debug"] = $Debug;
    $Configuration[".HashOnly"] = $HashOnly;
    $Configuration[".MetadataOnly"] = $MetadataOnly;
    $Configuration[".OutputFile"] = $Output;
    $Configuration[".IncludePaths"] = array_values(array_unique($includePaths));
    
    return ($Configuration);
}

