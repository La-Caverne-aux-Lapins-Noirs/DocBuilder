<?php

$CountVals = [];

function	C($rules)
{
    global	$CountVals;

    if (!isset($CountVals[$rules[1]]))
	$CountVals[$rules[1]] = 1;
    $tmp = $CountVals[$rules[1]];
    $CountVals[$rules[1]]++;
    return ($tmp);
}


