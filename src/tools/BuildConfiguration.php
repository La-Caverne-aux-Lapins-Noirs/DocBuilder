<?php

function BuildConfiguration($argc, $argv)
{
    $Cli = "";
    $Output = "a.out";
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
		exit(1);
	    }
	    $Output = $argv[++$i];
	}
	else
	    $Cli .= " {$argv[$i]}";
    }
    $Configuration = `mergeconf $Cli -of .json --resolve`;
    $Configuration = json_decode($Configuration, true);
    $Configuration[".Debug"] = $Debug;
    $Configuration[".OutputFile"] = $Output;
    return ($Configuration);
}
