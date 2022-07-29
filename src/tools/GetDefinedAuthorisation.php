 <?php

function GetDefinedAuthorisation($norm, $f, $label = false, $class = "", $speclabel = "")
{
    foreach (["Authorized", "Forbidden"] as $af)
	if (isset($norm[$af][$f]))
	    return (MakeAList($norm[$af][$f], $label ? Translate($speclabel.$f.$af) : "", $class));
    return ("");
}

