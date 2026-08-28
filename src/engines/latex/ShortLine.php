<?php

/**
 * Fixed-width inline form line.
 *
 * Unlike FLine, ShortLine does not consume the remaining line width and does
 * not start a new paragraph.  It is intended for short values embedded in a
 * sentence (dates, times, references, ...).
 *
 *   [#ShortLine;value]
 *   [#ShortLine;value;2.8]   // width in centimetres
 */
function ShortLine($rules)
{
    $content = isset($rules[1]) ? (string)$rules[1] : "";
    $width = 3.0;

    if (isset($rules[2]) && is_numeric($rules[2]))
        $width = (float)$rules[2];
    $width = max(0.8, min(12.0, $width));
    $width = rtrim(rtrim(number_format($width, 2, '.', ''), '0'), '.');

    $value = $content !== ""
        ? "\\rlap{\\color{black}\\textbf{".$content."}}"
        : "";

    return ("\\makebox[".$width."cm][l]{".
            $value.
            "\\color{lightgray}\\rule[-0.15ex]{".$width."cm}{0.35pt}}".
            "\\color{black}");
}
