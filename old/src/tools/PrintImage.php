<?php

// Affiche une image ou du HTML a la place
function PrintImage($cnf, $pic, $label, $label_replace = "", $props = "", $mandatory = false, $ctx = "Global", $css = false)
{
    global $Dictionnary;
    global $DocBuilder;

    if (NO_BASE64)
	return ("");

    $label = "";
    if ($mandatory)
    {
	$out = MustPrint($cnf, $pic, false, $ctx);
	if ($label != NULL)
	    $label = MustPrint($cnf, $label, true, $ctx);
    }
    else
    {
	$out = TryPrint($cnf, $pic, false, $ctx);
	if ($label != NULL)
	    $label = TryPrint($cnf, $label, true, $ctx);
    }
    if ($out != "")
    {
	if (($ext = strtolower(pathinfo($out, PATHINFO_EXTENSION))) == "jpeg")
	    $ext = "jpg";
	if (($file = @file_get_contents($out)) === false)
	{
	    $DocBuilder->Errors[] = "$ctx: File $out is missing";
	    return ("");
	}
	$out = base64_encode($file);
	if ($css == false)
	    return ("<img src='data:image/$ext;base64,$out' alt='$label' $props />\n");
	return ("url(data:image/$ext;base64,$out)");
    }
    if ($label != "" && $label_replace != "" && $css == false)
	return (str_replace("@@", $label, $label_replace)."\n");
    return ("");
}

