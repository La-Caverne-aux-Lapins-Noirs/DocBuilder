<?php

function DevelopPath($dir, array &$act, array $fields)
{
    if (substr($dir, 0, 1) == "/")
	return ;
    foreach ($fields as $f)
    {
	if (($final = ResolveAddress($act, $f)) == NULL)
	    continue ;
	if ($final == [])
	    $final = "";
	$final = $dir."/".$final;
    }
}
