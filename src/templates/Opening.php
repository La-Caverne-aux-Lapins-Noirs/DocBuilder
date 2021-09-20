<?php

function BuildPdfOpening()
{
    extract($GLOBALS);

    OpenDocument();
    if (!file_exists(__DIR__."/".tolower($DocBuilder->Format)."/"))
	continue ;

    if ($DocBuilder->Format == "PDFA4")
    {
	// Première page
	require_once (__DIR__."/template/big_cover.php");
	StartSubRecord();
	require_once (__DIR__."/template/global_medal.php");
	StopSubRecord();
	StartSubRecord();
	require_once (__DIR__."/template/table_of_content.php");
	StopSubRecord();
	return ;
    }
    if ($DocBuilder->Format == "PDFA5")
    {
	// Première page
	require_once (__DIR__."/template/small_cover.php");
	require_once (__DIR__."/template/table_of_content.php");
	return ;
    }

}
