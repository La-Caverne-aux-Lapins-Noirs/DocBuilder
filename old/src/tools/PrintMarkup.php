<?php

// Affiche un bout de HTML avec un element précisé, traduisible, a chercher dans la conf
function PrintMarkup($cnf, $name, $html, $text = true, $mandatory = false, $ctx = "Global")
{
    if ($mandatory)
	$out = MustPrint($cnf, $name, $text, $ctx);
    else
	$out = TryPrint($cnf, $name, $text, $ctx);
    if ($out != "")
	echo str_replace("@@", $out, $html)."\n";
}

