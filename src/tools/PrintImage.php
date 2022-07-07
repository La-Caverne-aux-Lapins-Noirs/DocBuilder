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
    {
	if (($ext = strtolower(pathinfo($out, PATHINFO_EXTENSION))) == "jpeg")
	    $ext = "jpg";
	$out = base64_encode(file_get_contents($out));
	echo "<img src='data:image/$ext;base64,$out' alt='$label' $props />\n";
    }
    else if ($label != "" && $label_replace != "")
	echo str_replace("@@", $label, $label_replace)."\n";
}

