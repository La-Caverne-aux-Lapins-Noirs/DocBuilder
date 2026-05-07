<?php

function LatexEscape(string $s): string
{
    // Échappement minimal LaTeX
    $map = [
        '\\' => '\\textbackslash{}',
        '{'  => '\\{',
        '}'  => '\\}',
        '$'  => '\\$',
        '&'  => '\\&',
        '#'  => '\\#',
        '_'  => '\\_',
        '%'  => '\\%',
        '~'  => '\\textasciitilde{}',
        '^'  => '\\textasciicircum{}',
    ];
    $s = strtr($s, $map);
    // Nettoyage des retours ligne dans les cellules
    $s = preg_replace("/\r\n|\r|\n/", " ", $s);
    return trim($s);
}

