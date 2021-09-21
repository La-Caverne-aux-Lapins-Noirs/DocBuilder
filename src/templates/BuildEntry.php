<?php

function BuildEntry($Ex, $Num)
{
    extract($GLOBALS);

    // On établis des variables remarquables utilisables dans les modèles
    
    $Title = isset($Ex[$Field = "Name"]) ? $Ex[$Field] : "";
    $Document = isset($Ex[$Field = "Document"]) ? $Ex[$Field] : [];

    $Fields = [
	"FilesToDeliver",
	"SourceDirectories",
	"IncludeDirectories",
	"CompileFlags"
    ];
    $Table = false;
    foreach ($Fields as $f)
    {
	$Document[$f] = MustBeAnArray($Document[$f]);
	if (count($Document[$f]) > 3)
	    $Table = true;
    }

    // On commence la génération de l'entrée
    require (__DIR__."/".strtolower($DocBuilder->Format)."/entry.php");

    // On parcoure
    if (isset($Document["Texts"]))
    {
	foreach ($Document["Texts"] as $text)
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
    }

    // On effectue l'eventuelle cloture
    if (file_exists($file = __DIR__."/".strtolower($DocBuilder->Format)."/post_entry.php"))
	require ($file);
}
