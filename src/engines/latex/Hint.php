<?php

function Hint(array $rules): string
{
    if (!(isset($rules[1])))
        return "";
    $str = "\\begin{tcolorbox}[colback=cyan, coltext=black, sharp corners=southwest]\n";
    $str .= implode(";", array_slice($rules, 1))."\n";
    $str .= "\\end{tcolorbox}";
    return $str;
}