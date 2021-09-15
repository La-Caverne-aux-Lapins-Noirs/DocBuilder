<?php // @codeCoverageIgnoreStart

function StartSubRecord($open = false)
{
    ob_start("SubKeepContent");
    if ($open)
	OpenPage();
    return (true);
}

// @codeCoverageIgnoreEnd
