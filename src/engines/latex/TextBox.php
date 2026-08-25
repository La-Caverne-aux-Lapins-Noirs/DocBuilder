<?php

function TextBox($rules)
{
    $height = 4.0;
    if (isset($rules[1]) && is_numeric($rules[1]))
        $height = max(0.5, (float)$rules[1]);

    $content = isset($rules[2]) ? $rules[2] : "";
    $label = isset($rules[3]) ? trim((string)$rules[3]) : "";

    $body = "";
    if ($label !== "")
        $body .= "\\textbf{".LatexEscape($label)."}\\par\\vspace{0.15cm}\n";
    $body .= $content;

    return ("\\noindent\\makebox[\\linewidth][l]{%\n".
            "\\fbox{%\n".
            "\\parbox[t][".$height."cm][t]{\\dimexpr\\linewidth-2\\fboxsep-2\\fboxrule\\relax}{%\n".
            $body."\n".
            "}\n".
            "}%\n".
            "}\\par\\medskip\n");
}
