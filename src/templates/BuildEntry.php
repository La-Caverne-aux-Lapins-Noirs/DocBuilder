<?php

function BuildEntry($ex, $Num)
{
    extract($GLOBALS);

    // On établis des variables remarquables utilisables dans les modèles
    $Title = MergeNumber($Num)." - ".$ex["Name"];
    $Document = $ex["Document"];

    $Fields = [
	"FilesToDeliver",
	"SourceDirectories",
	"IncludeDirectories",
	"CompileFlags"
    ];
    $Table = false;
    foreach ($fields as $f)
	if (count($Document[$f]) > 3)
	    $Table = true;

    // On commence la génération de l'entrée
    require (__DIR__."/template/entry.php");
    foreach ($document["Texts"] as $text)
    {
	if (isset($text["Prototype"]))
	{
	    $Prototype = $text["Prototype"];
	    require (__DIR__."/template/prototype.php");
	}
	else
	{
	    $Text = implode("", $text);
	    require (__DIR__."/template/text.php");
	}
    }

    // On effectue l'eventuelle cloture
    if (file_exists($file = __DIR__."/".strtolower($DocBuilder->Format)."/post_entry.php"))
	require ($file);
}
