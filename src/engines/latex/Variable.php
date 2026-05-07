<?php

function Variable($rules)
{
    global $Configuration;

    $tmp = $Configuration;
     $rules = explode(".", $rules[1]);
    foreach ($rules as $r)
    {
	if (!isset($tmp[$r]))
	    return ("");
	$tmp = $tmp[$r];
    }
    return ($tmp);
}


