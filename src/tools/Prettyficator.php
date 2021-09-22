<?php

function Prettyficator($html)
{
    // return ($html);
    $Config = [
	"indent" => true,
	"output-xhtml" => false,
	"wrap" => 0,
	"char-encoding" => "utf8",
	"output-encoding" => "utf8",
    ];
    $Tidy = new tidy;
    $Tidy->parseString($html, $Config, "utf8");
    $Tidy->cleanRepair();
    return (tidy_get_output($Tidy)."\n");
}
