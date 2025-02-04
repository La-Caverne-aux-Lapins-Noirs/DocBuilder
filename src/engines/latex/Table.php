<?php

function Table($rules)
{
    // Pandoc est beaucoup plus naze que ce que j'esperais.
    $spaces = "";
    for ($i = 0; $i < $rules[1]; ++$i)
    {
	if ($i + 1 < $rules[1])
	    $spaces .= "\\ ";
	else
	    $spaces .= "\\mbox{}";
    }
    return (str_replace(
	[" | ", "\n| ", " |\n"],
	["$spaces|$spaces", "\n|$spaces", "$spaces|\n"],
	$rules[2]
    ));
}

