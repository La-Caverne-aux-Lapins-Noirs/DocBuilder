<?php

function	Columns($rules)
{
    if ($rules[1] == 1)
	$str = "\\begin{minipage}{\\dimexpr\\linewidth-2\\columnsep}".ELine($rules);
    else
	$str = "\\begin{multicols}{{$rules[1]}}\sloppy";
    
    for ($i = 2; isset($rules[$i]); ++$i)
	$str .= $rules[$i];
    
    if ($rules[1] == 1)
	$str .= "\\end{minipage}";
    else
	$str .= "\\end{multicols}";
    return ($str);
}

