<?php

function IsArray($arr)
{
    if (!is_array($arr))
	return false;
    if (count($arr) == 0)
	return false;
    return array_keys($arr) !== range(0, count($arr) - 1);
}

