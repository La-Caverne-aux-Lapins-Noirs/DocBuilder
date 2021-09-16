<?php // @codeCoverageIgnoreStart

function StartSubRecord($open = false)
{
    ob_start("SubKeepContent");
    return (true);
}

// @codeCoverageIgnoreEnd
