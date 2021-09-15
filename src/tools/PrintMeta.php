<?php

// Affiche un meta HTML
function PrintMeta($cnf, $name, $cname, $mandatory = false)
{
    if ($mandatory)
	$out = MustPrint($cnf, $cname);
    else
	$out = TryPrint($cnf, $cname);
    if ($out != "")
	echo "<meta name='$name' content='$out' />\n";
}
