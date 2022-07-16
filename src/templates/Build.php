<?php

function Build()
{
    extract($GLOBALS);

    $Depth = [];
    if (!file_exists(($dire = __DIR__."/".strtolower($DocBuilder->Code))))
    {
	$DocBuilder->Errors[] = "Global: {$DocBuilder->Type} is not a supported document generator.";
	return ;
    }
    if (!file_exists(($dirt = __DIR__."/".strtolower($DocBuilder->Type))))
    {
	$DocBuilder->Errors[] = "Global: {$DocBuilder->Type} is not a supported document type.";
	return ;
    }

    if (file_exists(($file = "$dire/open_document.php")))
	require_once ($file);

     require_once ("$dirt/build.php");

    if (file_exists(($file = "$dire/close_document.php")))
	require_once ($file);
}
