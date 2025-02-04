<?php

function GetDefinedAuthorisation($norm, $f, $label = false, $class = "", $speclabel = "")
{
    foreach (["Authorized", "Forbidden"] as $af)
	if (isset($norm[$f][$af]))
	    return (MakeAList($norm[$f][$af], $label ? Translate($speclabel.$f.$af) : "", $class));
    return ("");
}

