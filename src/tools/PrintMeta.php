<?php

// Affiche un meta HTML
function PrintMeta($cnf, $name, $cname, $mandatory = false)
{
    if ($mandatory)
	$out = MustPrint($cnf, $cname);
    else
	$out = TryPrint($cnf, $cname);
    $name = htmlentities($name);
    $out = htmlentities($out);
    if ($out != "")
	echo "<meta name='$name' content='$out' />\n";
}
