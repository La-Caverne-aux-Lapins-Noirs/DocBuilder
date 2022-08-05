<?php

function MakeAList($lst, $hd = "", $class = "")
{
    if (count($lst) == 0 || $lst == NULL)
	return ("");
    $gen = [];
    if ($hd != "")
	$gen[] = "<p style='font-weight: bold;'>$hd:<br /></p>";
    $gen[] = "<ul class='$class'>";
    foreach ($lst as $l)
	$gen[] = "<li>$l</li>";
    $gen[] = "</ul>";
    return (implode($gen));
}

