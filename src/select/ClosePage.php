<?php // @codeCoverageIgnoreStart

function ClosePage()
{
    extract($GLOBALS);

    if ($DocBuilder->Format == "PDFA4")
	require (__DIR__."/../pdf/template/big_bottom.php");
    else if ($DocBuilder->Format == "PDFA5")
	require (__DIR__."/../pdf/template/small_bottom.php");
    $PageCount += 1;
}

// @codeCoverageIgnoreEnd
