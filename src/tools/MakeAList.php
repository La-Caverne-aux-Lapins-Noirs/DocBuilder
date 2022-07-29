<?php

function MakeAList($lst, $hd = "", $class = "")
{
    if (count($lst) == 0)
	return ("");
    $gen = [];
    if ($hd != "")
	$gen[] = "<p>$hd:<br /><br /></p>";
    $gen[] = "<ul class='$class'>";
    foreach ($lst as $l)
	$gen[] = "<li>$l</li>";
    $gen[] = "</ul>";
    return (implode($gen));
}

