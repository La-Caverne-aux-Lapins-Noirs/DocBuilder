<?php

function ClosePage()
{
    extract($GLOBALS);

    if ($DocBuilder->Format == "PDFA4")
	require (__DIR__."/template/big_bottom.php");
    else if ($DocBuilder->Format == "PDFA5")
	require (__DIR__."/template/small_bottom.php");
    $PageCount += 1;
}

