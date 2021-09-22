<?php

function LogText($txt = NULL)
{
    if (0) // Une fois qu'on a plus besoin de debugger...
	return ;

    $logfile = "/tmp/log";
    if ($txt == NULL)
	file_put_contents($logfile, "");
    else
    {
	$log = file_get_contents($logfile);
	file_put_contents($logfile, $log.$txt."\n");
    }
}

function LogTextR($obj)
{
    LogText(print_r($obj, true));
}
