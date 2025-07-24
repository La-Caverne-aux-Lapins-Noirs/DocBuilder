<?php

function Choice($rules)
{
    $str = "";
    array_shift($rules);
    foreach ($rules as $v)
	$str .= CheckBox([])." $v ";
    return ($str);
}

