<?php // @codeCoverageIgnoreStart

function StartSubRecord($open = false)
{
    global $DocBuilder;

    $DocBuilder->SubRecording = true;
    ob_start("SubKeepContent");
    if ($open)
	OpenPage();
    return (true);
}

// @codeCoverageIgnoreEnd
