<?php

function LoadFile(&$out, $opts, $long, $short, $default = NULL, $dabsic = true)
{
    global $argv;

    if (($File = GetOption($opts, $long, $short, $default)) === NULL)
    {
	echo sprintf("$argv[0]: Missing parameter: $long configuration (-$short file / --$long=file).\n");
	return (false);
    }
    if ($File === "")
	return (false);
    if ($dabsic)
    {
	if (!($out = LoadDabsic($File)))
	{
	    echo sprintf("$argv[0]: Invalid $long file $File.\n");
	    return (false);
	}
    }
    else
    {
	if (!($out = @file_get_contents($File)))
	{
	    echo sprintf("$argv[0]: Invalid $long file $File.\n");
	    return (false);
	}
    }
    return (true);
}
