<?php

$ToClose = [];
function CloseFiles()
{
    global $ToClose;

    foreach ($ToClose as $k => $v)
    {
	fclose($v);
	// unlink($k);
    }
}

function TempDab($data, $ext = ".dab")
{
    global $ToClose;

    $tmp = "./.".uniqid().$ext;
    if (($h = fopen($tmp, "w")) === false)
	return (false);
    fwrite($h, $data);
    $ToClose[$tmp] = $h;
    return ($tmp);
}

register_shutdown_function("CloseFiles");
