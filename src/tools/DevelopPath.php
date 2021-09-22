<?php

function DevelopPath($dir, array &$act, array $fields)
{
    if (substr($dir, 0, 1) == "/")
	return ;
    foreach ($fields as $f)
    {
	if (isset($act[$f]))
	{
	    $act[$f] = $dir."/".$act[$f];
	}
    }
}
