<?php // @codeCoverageIgnoreStart

function OpenPage()
{
    extract($GLOBALS);

    if ($DocBuilder->Format == "PDFA4")
	require (__DIR__."/../pdf/template/big_top.php");
    else if ($DocBuilder->Format == "PDFA5")
	require (__DIR__."/../pdf/template/small_top.php");
}

// @codeCoverageIgnoreEnd
