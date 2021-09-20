<?php // @codeCoverageIgnoreStart

function StartSubRecord($open = false)
{
    global $DocBuilder;

    $DocBuilder->SubRecording = true;
    ob_start("SubKeepContent");
    if ($open && file_exists($file = __DIR__."/".strtolower($DocBuilder->Format)."/top.php"))
	require ($file);
    return (true);
}

// @codeCoverageIgnoreEnd
