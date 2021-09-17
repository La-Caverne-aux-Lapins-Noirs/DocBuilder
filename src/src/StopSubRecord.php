<?php // @codeCoverageIgnoreStart

function StopSubRecord($close = false)
{
    global $SubOutput;
    global $DocBuilder;

    $DocBuilder->SubRecording = false;
    if ($DocBuilder->Format == "html")
	return (true);
    if ($close)
	ClosePage();
    $SubOutput = $SubOutput.ob_get_contents();
    ob_end_clean();
    Paginize($SubOutput);
    $SubOutput = "";
    return (true);
}

// @codeCoverageIgnoreEnd
