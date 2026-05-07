<?php

function	Size($rules)
{
    $rules[1] = [
	0 => "\\tiny",
	1 => "\\scriptsize",
	2 => "\\footnotesize",
	3 => "\\small",
	4 => "\\normalsize",
	5 => "\\large",
	6 => "\\Large",
	7 => "\\LARGE",
	8 => "\\huge",
	9 => "\\Huge",
    ][(int)$rules[1]];
    return ("{$rules[1]}");
}

