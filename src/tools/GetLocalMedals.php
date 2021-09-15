<?php

function GetLocalMedals($array)
{
    $out = [];
    foreach ($array as $ex)
    {
	if (isset($ex["Medals"]))
	{
	    $ex["Medals"] = MustBeAnArray($ex["Medals"]);
	    foreach ($ex["Medals"] as $med)
	    {
		if (!isset($out[$med]))
		    $out[$med] = 0;
		$out[$med] += 1;
	    }
	}
	else if (IsArray($ex))
	{
	    $sub = GetLocalMedals($ex);
	    foreach ($sub as $k => $s)
	    {
		if (!isset($out[$k]))
		    $out[$k] = $s;
		else
		    $out[$k] += $s;
	    }
	}
    }
    return ($out);
}
