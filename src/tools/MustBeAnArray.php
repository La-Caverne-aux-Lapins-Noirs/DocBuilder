<?php

function MustBeAnArray(&$val)
{
    if (!isset($val) || $val == NULL)
	return ($val = []);
    if (is_array($val))
	return ($val);
    return ($val = [$val]);
}

