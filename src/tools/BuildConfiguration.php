<?php

function RunMergeconf(array $args): string
{
    $cmd = array_merge(["mergeconf"], $args, ["-of", ".json", "--resolve"]);
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
    $Output = "a.out.pdf";
    $Debug = false;
    for ($i = 1; $i < $argc; ++$i)
    {
        if ($argv[$i] == "-d")
            $Debug = true;
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

    $json = RunMergeconf($Cli);
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
    
    // mergeconf remains the single parser/resolver for all Dabsic input.
    // Once the complete tree exists, normalize generic signatory declarations
    // into the Signatories.<Role> scopes consumed by document renderers.
    ResolveSignatories($Configuration);

    $Configuration[".Debug"] = $Debug;
    $Configuration[".OutputFile"] = $Output;
    
    return ($Configuration);
}

