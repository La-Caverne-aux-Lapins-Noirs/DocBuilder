<?php

function BuildEntry($ex, $num)
{
    extract($GLOBALS);

    $Ex = $ex;
    $Num = $num;
    if ($DocBuilder->SubRecording == false)
	StartSubRecord(true);

    // On établis des variables remarquables utilisables dans les modèles
    if (($Title = isset($Ex[$Field = "Name"]) ? $Ex[$Field] : "") !== "")
	$DocBuilder->ExercicePage[Translate($Title)] = $PageCount;
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
	if (!isset($Document[$f]))
	    continue ;
	$Document[$f] = MustBeAnArray($Document[$f]);
	if (count($Document[$f]) > 3)
	    $Table = true;
    }

    // On commence la génération de l'entrée
    require (__DIR__."/".strtolower($DocBuilder->Type)."/entry.php");

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
    if (file_exists($file = __DIR__."/".strtolower($DocBuilder->Type)."/post_entry.php"))
	require ($file);
}
