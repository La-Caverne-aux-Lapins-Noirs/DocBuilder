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
    $json = stream_get_contents($pipes[1]); fclose($pipes[1]);
    $err = stream_get_contents($pipes[2]); fclose($pipes[2]);
    $code = proc_close($proc);
    if ($code !== 0)
        throw new RuntimeException("mergeconf failed with code $code:\n".$err);
    return ($json);
}

function BuildConfiguration($argc, $argv)
{
    $Cli = [];
    $Blank = false;
    $HashOnly = false;
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

    $Configuration[".Debug"] = $Debug;
    $Configuration[".HashOnly"] = $HashOnly;
    $Configuration[".OutputFile"] = $Output;
    
    return ($Configuration);
}

