<?php

function Bonus(array $rules): string
{
    if (!(isset($rules[1])))
        return "";
    $str = "\\begin{tcolorbox}[colback=yellow, coltext=black, sharp corners=southwest]\n";
    $str .= $rules[1]."\n";
    $str .= "\\end{tcolorbox}";
    return $str;
}