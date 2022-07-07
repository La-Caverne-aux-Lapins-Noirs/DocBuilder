<?php

function _Merge($sep, $data)
{
    if (!is_array($data))
	return ($data);
    foreach ($data as $d)
    {
	$d = Merge($d);
    }
    return (implode($sep, $data));
}

function Merge($a, $b = NULL)
{
    if ($b == NULL)
	return (_Merge("", $a));
    return (_Merge($a, $b));
}

