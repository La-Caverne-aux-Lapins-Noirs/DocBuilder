<?php

function empty_cell($v)
{
    return (trim($v) !== "");
}

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
    // On colle des espaces dans la ligne des labels
    $table = $rules[2];
    $table = explode("\n", $table);
    $table = array_filter($table, "empty_cell");
    $table = array_values($table);
    $table[0] = str_replace(
	[" | ", "\n| ", " |\n"],
	["$spaces|$spaces", "\n|$spaces", "$spaces|\n"],
	$table[0]
    );
    return (implode("\n", $table));
}

