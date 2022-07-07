<?php

function Repeat($c, $s)
{
    $out = "";
    for ($i = 0; $i < $s; ++$i)
	$out .= $c;
    return ($out);
}

