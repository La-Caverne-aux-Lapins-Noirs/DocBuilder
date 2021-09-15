<?php // @codeCoverageIgnoreStart

function StopSubRecord($close = false)
{
    global $SubOutput;

    if ($close)
	ClosePage();
    $SubOutput = $SubOutput.ob_get_contents();
    ob_end_clean();
    Paginize($SubOutput);
    $SubOutput = "";
    return (true);
}

// @codeCoverageIgnoreEnd
