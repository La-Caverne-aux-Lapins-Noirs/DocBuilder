<?php

function _row_weights($count, $spec)
{
    $weights = array_fill(0, $count, 1.0);
    if (!is_string($spec) || trim($spec) === "")
        return ($weights);

    $spec = trim($spec);
    if (strlen($spec) >= 2 && (($spec[0] === '"' && $spec[strlen($spec) - 1] === '"') ||
        ($spec[0] === "'" && $spec[strlen($spec) - 1] === "'")))
        $spec = substr($spec, 1, -1);

    $parts = preg_split('/\\s*,\\s*/', trim($spec));
    if (count($parts) != $count)
        return ($weights);

    foreach ($parts as $i => $part)
    {
        if (!is_numeric($part) || (float)$part <= 0)
            return ($weights);
        $weights[$i] = (float)$part;
    }
    return ($weights);
}

function Row($rules)
{
    if (!isset($rules[1]) || !is_numeric($rules[1]))
        return ("");

    $count = max(1, (int)$rules[1]);
    $weights = _row_weights($count, $rules[2] ?? "");
    $sum = array_sum($weights);
    $available = 1.0 - (($count - 1) * 0.02);

    $out = "\\noindent\n";
    for ($i = 0; $i < $count; ++$i)
    {
        $ratio = $available * $weights[$i] / $sum;
        $content = $rules[$i + 3] ?? "";

        // A Row is often used with reusable text fragments stored in Dabsic
        // variables. The outer directive parser has already resolved the
        // [#Variable;...] call itself, but directives contained in the value
        // returned by that variable still need one pass of resolution.
        // Keep this behaviour local to Row: ordinary variables remain plain
        // data everywhere else.
        if (is_string($content) &&
            (str_contains($content, "[@") || str_contains($content, "[#")))
        {
            global $Configuration;
            $content = ResolveDirectives($Configuration, $content);
        }

        $out .= "\\begin{minipage}[t]{".number_format($ratio, 5, '.', '')."\\linewidth}\n";
        $out .= "\\vspace{0pt}\n".$content."\n";
        $out .= "\\end{minipage}\n";
        if ($i + 1 < $count)
            $out .= "\\hfill\n";
    }
    return ($out."\\par\n");
}
