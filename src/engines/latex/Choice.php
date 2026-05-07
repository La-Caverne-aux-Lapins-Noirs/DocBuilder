<?php

function Choice($rules)
{
    $str = "";
    $var = $rules[0];
    array_shift($rules);
    foreach ($rules as $v)
	$str .= CheckBox(["", $v == $var])." $v ";
    return ($str);
}

