<?php

function YesNo($rules)
{
    $val = "";
    if (@$rules[1] != "")
    {
	$val = $rules[1];
	if ($val == "Yes" || $val == "true" || $val === true || $val == 1)
	    $val = "Oui";
	else
	    $val = "Non";
    }
    return (Choice([$val, "Oui", "Non"]));
}

