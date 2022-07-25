 <?php

function GetDefinedAuthorisation($norm, $f, $label = false, $class = "")
{
    foreach (["Authorized", "Forbidden"] as $af)
	if (isset($norm[$af][$f]))
	    return (MakeAList($norm[$af][$f], $label ? Translate($f.$af) : "", $class));
    return ("");
}

