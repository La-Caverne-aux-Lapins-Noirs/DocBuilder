<?php

function BuildPdfOpening()
{
    extract($GLOBALS);

    OpenDocument();

    // Première page
    if ($DocBuilder->Format == "PDFA4")
	require_once (__DIR__."/template/big_cover.php");
    else if ($DocBuilder->Format == "PDFA5")
	require_once (__DIR__."/template/small_cover.php");

    // Table des matières
    StartSubRecord();
    require_once (__DIR__."/template/table_of_content.php");
    StopSubRecord();
}
