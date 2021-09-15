<?php

// Affiche une image ou du HTML a la place
function PrintImage($cnf, $pic, $label, $label_replace = "", $props = "", $mandatory = false, $ctx = "Global")
{
    global $Dictionnary;

    if ($mandatory)
    {
	$out = MustPrint($cnf, $pic, false, $ctx);
	$label = MustPrint($cnf, $label, true, $ctx);
    }
    else
    {
	$out = TryPrint($cnf, $pic, false, $ctx);
	$label = TryPrint($cnf, $label, true, $ctx);
    }
    if ($out != "")
	echo "<img src='$out' alt='$label' $props />\n";
    else if ($label != "" && $label_replace != "")
	echo str_replace("@@", $label, $label_replace)."\n";
}

