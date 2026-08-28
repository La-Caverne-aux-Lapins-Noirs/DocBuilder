<?php

/**
 * Force a first-line indent at the current position.
 *
 *   [@Indent]       -> 1 cm
 *   [@Indent;1.5]   -> 1.5 cm
 *
 * The empty mbox keeps the following hspace in the Markdown paragraph when
 * Pandoc converts the document to LaTeX.
 */
function Indent($rules)
{
    $width = 1.0;
    if (isset($rules[1]) && is_numeric($rules[1]))
        $width = (float)$rules[1];
    $width = max(0.0, min(10.0, $width));
    $width = rtrim(rtrim(number_format($width, 2, '.', ''), '0'), '.');
    if ($width === '')
        $width = '0';

    return ("\\mbox{}\\hspace*{".$width."cm}");
}
