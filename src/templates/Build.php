<?php

function Build()
{
    extract($GLOBALS);

    $Depth = [];
    if (!file_exists(($dir = __DIR__."/".strtolower($DocBuilder->Format))))
	return ;

    if (file_exists(($file = __DIR__."/misc/open_document_".$DocBuilder->Code.".php")))
	require_once ($file);

    if (file_exists(($file = $dir."/cover.php")))
	require_once ($file);
    if (file_exists(($file = $dir."/global_medal.php")))
	require_once ($file);
    /*
    if (file_exists(($file = $dir."/table_of_content.php")))
	require_once ($file);

    BrowseExercises($DocBuilder->Activity["Exercises"], $Depth, "BuildEntry");
     */

    if (file_exists(($file = __DIR__."/misc/close_document_".$DocBuilder->Code.".php")))
	require_once ($file);
}
