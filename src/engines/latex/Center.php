<?php

function Center($rules)
{
    if (count($rules) == 1)
	return ("\centering");
    $str = "\\begin{center}";
    $str .= $rules[1];
    $str .= "\\end{center}";
    return ($str);
}

