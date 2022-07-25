<?php

function MakeAList($lst, $hd = "", $class = "")
{
    if (count($lst) == 0)
	return ("");
    $gen = ["<ul class='$class'>"];
    if ($hd != "")
	$gen[] = $hd.":";
    foreach ($lst as $l)
	$gen[] = "<li>$l</li>";
    $gen[] = "</ul>";
    return (implode($gen));
}

