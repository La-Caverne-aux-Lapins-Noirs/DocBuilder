<?php

function MergeNumber($num)
{
    $num[0] = ToRoman($num[0]);
    if (count($num) > 1)
	$num[0] = array_shift($num)." ".$num[0];
    return (implode(".", $num));
}

