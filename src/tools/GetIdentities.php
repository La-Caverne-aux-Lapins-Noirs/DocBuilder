<?php

function GetIdentities($nod)
{
    $gen = ["<ul>"];
    foreach ($nod as $couple)
    {
	$gen[] = "<li>".$couple["Name"];
	$gen[] = " &lt;".$couple["Mail"]."&gt;</li>";
    }
    $gen[] = "</ul>";
    return (implode($gen));
}

