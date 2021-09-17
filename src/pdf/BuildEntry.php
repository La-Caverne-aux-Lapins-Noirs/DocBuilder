<?php

function BuildEntry($ex, $Num)
{
    extract($GLOBALS);

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
}
