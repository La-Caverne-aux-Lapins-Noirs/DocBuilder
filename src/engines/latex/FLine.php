<?php

function	FLine($rules)
{
    $l = "";
    if (isset($rules[1]))
	$l = "\\rlap{\\color{black}\\textbf{".$rules[1]."}}";
    return ("\\color{lightgray} $l\\hrulefill\n\n\\color{black}\n");
}

