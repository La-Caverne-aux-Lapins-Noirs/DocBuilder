<?php

function BuildPdfOpening()
{
    extract($GLOBALS);

    OpenDocument();
    if ($DocBuilder->Format == "PDFA4")
    {
	require_once (__DIR__."/template/big_cover.php");
	AddChapter(["Type" => "Builtin", "Document" => __DIR__."/template/summary.php", "Name" => "Index"], [], 0);
    }
    else if ($DocBuilder->Format == "PDFA5")
    {
	require_once (__DIR__."/template/small_cover.php");
    }
}
