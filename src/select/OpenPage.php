<?php // @codeCoverageIgnoreStart

function OpenPage()
{
    extract($GLOBALS);

    if ($DocBuilder->Format == "PDFA4")
	require (__DIR__."/../pdf/template/big_top.php");
}

// @codeCoverageIgnoreEnd
