<?php

function &_ResolveAddress(&$act, $addr)
{
    if ($act == NULL)
	return ($act);
    $first = array_shift($addr);
    if (count($addr) == 0)
	return (ResolveAddress($act, $first));
    return (_ResolveAddress(ResolveAddress($act, $first), $addr));
}

function &ResolveAddress(&$act, $addr)
{
    if (!is_array($addr))
    {
	if (!is_array($act))
	    $act = [];
	if (!isset($act[$addr]))
	    $act[$addr] = [];
	return ($act[$addr]);
    }
    return (_ResolveAddress($act, $addr));
}

