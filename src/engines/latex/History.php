<?php

function History(array $rules): string
{
    if (!isset($rules[1]))
        return "";
    return "\\begin{tcolorbox}[colback=gray!10, coltext=black, sharp corners=southwest]\n".
        implode(";", array_slice($rules, 1))."\n\\end{tcolorbox}";
}
