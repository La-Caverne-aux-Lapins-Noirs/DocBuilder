<?php

function ResolveDirectives($conf, $str, $pfx = "[@")
{
    $out = "";
    for ($i = 0, $len = strlen($str); $i < $len; ++$i)
    {
	if (substr($str, $i, 2) == $pfx)
	{
	    $i += 2;
	    $count = 0;
	    for ($j = $i; $j < $len && ($str[$j] != ']' || $count > 0); ++$j)
	    {
		if ($str[$j] == '[')
		    $count += 1;
		else if ($str[$j] == ']')
		    $count -= 1;
	    }
	    if ($j == $len)
	    {
		echo "Missing ']' after directive ".substr($str, $i, 20)."\n";
		return (NULL);
	    }
	    $directive = substr($str, $i, $j - $i);
	    $directive = explode(";", $directive);
	    $out .= $directive[0]($directive);
	    $i = $j;
	}
	else
	    $out .= $str[$i];
    }
    return ($out);
}

