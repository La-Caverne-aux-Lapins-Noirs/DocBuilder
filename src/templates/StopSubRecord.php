<?php // @codeCoverageIgnoreStart

function StopSubRecord($close = false)
{
    global $SubOutput;
    global $DocBuilder;

    $DocBuilder->SubRecording = false;
    if ($close && file_exists($file = __DIR__."/".strtolower($DocBuilder->Format)."/bottom.php"))
	require ($file);
    $SubOutput = $SubOutput.ob_get_contents();
    ob_end_clean();
    Paginize($SubOutput);
    $SubOutput = "";
    return (true);
}

// @codeCoverageIgnoreEnd
