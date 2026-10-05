<?php

function Warning(array $rules): string
{
    if (!isset($rules[1]))
        return "";
    return "\\begin{tcolorbox}[colback=red!10, coltext=black, sharp corners=southwest]\n".
        implode(";", array_slice($rules, 1))."\n\\end{tcolorbox}";
}
