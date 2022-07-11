<?php

$ToClose = [];
function CloseFiles()
{
    global $ToClose;

    foreach ($ToClose as $k => $v)
    {
	fclose($v);
	unlink($k);
    }
}

function TempDab($data, $ext = ".dab")
{
    global $ToClose;

    $tmp = tempnam(sys_get_temp_dir(), "");
    $ntmp = "$tmp$ext";
    rename($tmp, $ntmp);
    $h = fopen($ntmp, "w");
    fwrite($h, $data);
    $ToClose[$ntmp] = $h;
    return ($ntmp);
}

register_shutdown_function("CloseFiles");
