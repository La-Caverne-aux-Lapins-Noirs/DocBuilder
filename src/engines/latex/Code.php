<?php

/** Render a literal source body; Dabsic interpolation has already happened. */
function Code(array $rules): string
{
    if (!isset($rules[1]))
        throw new Exception("Rules is missing for code Function !");
    $language = trim($rules[1]);
    // Also accept callers that still supply semicolon-separated body parts.
    $body = implode(';', array_slice($rules, 2));
    $str = "\\begin{tcolorbox}[colback=black, coltext=white, sharp corners=southwest]\n";
    $str .= "\\begin{minted}{".$language."}\n".$body."\n";
    $str .= "\\end{minted}\n\\end{tcolorbox}";
    // main() trims lines before compiling. Preserve code indentation there.
    return str_replace([" ", "\t"], ['\\sp', '\\tb'], $str);
}
