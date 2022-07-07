<?php

function LoadDabsic($file)
{
    global $argv;

    if (is_array($file))
    {
	foreach ($file as &$f)
	    if (is_array($f))
		$f = implode(" ", $f);
	$file = implode(" ", $file);
    }
    if (($json = @shell_exec("mergeconf -i $file -of .json --resolve 2> /dev/null")) == NULL)
    {
	echo "$argv[0]: Failed to load $file.";
	return (NULL);
    }
    if (!($json = json_decode($json, true)))
    {
	echo "$argv[0]: Failed to load $file: ".json_last_error_msg();
	return (NULL);
    }
    return ($json);
}

