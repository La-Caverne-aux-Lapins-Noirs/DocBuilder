<?php

function OpenPage()
{
    extract($GLOBALS);

    if ($DocBuilder->Format == "PDFA4")
	require (__DIR__."/template/big_top.php");
}

