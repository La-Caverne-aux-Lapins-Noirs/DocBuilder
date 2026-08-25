<?php

function empty_cell($v)
{
    return (trim($v) !== "");
}

function table_column_weights($value, $columns)
{
    if ($value === NULL || trim((string)$value) === "")
        return (array_fill(0, $columns, 1.0));

    $parts = array_map("trim", explode(",", (string)$value));
    if (count($parts) != $columns)
        throw new RuntimeException("Table column proportions must contain exactly $columns values.");

    $weights = [];
    foreach ($parts as $part)
    {
        if (!is_numeric($part) || (float)$part <= 0)
            throw new RuntimeException("Table column proportions must be positive numbers.");
        $weights[] = (float)$part;
    }
    return ($weights);
}

function table_separator_cell($cell, $dash_count)
{
    $cell = trim($cell);
    $left = str_starts_with($cell, ":");
    $right = str_ends_with($cell, ":");
    $dash_count = max(3, (int)$dash_count);
    return (($left ? ":" : "").str_repeat("-", $dash_count).($right ? ":" : ""));
}

function table_wide_separator($line, $columns, $proportions)
{
    $weights = table_column_weights($proportions, $columns);
    $sum = array_sum($weights);
    $cells = explode("|", trim($line));
    if (count($cells) >= 2 && trim($cells[0]) === "")
        array_shift($cells);
    if (count($cells) >= 1 && trim($cells[count($cells) - 1]) === "")
        array_pop($cells);
    if (count($cells) != $columns)
        throw new RuntimeException("Table separator does not contain $columns columns.");

    $out = [];
    // Pandoc uses the relative number of dashes in pipe-table separators as
    // column widths. A deliberately large total makes it emit explicit
    // p{...} widths spanning the available column width.
    $total_dashes = 100;
    foreach ($cells as $i => $cell)
    {
        $count = (int)round($total_dashes * $weights[$i] / $sum);
        $out[] = table_separator_cell($cell, $count);
    }
    return ("|".implode("|", $out)."|");
}

function Table($rules)
{
    $columns = (int)$rules[1];
    if ($columns <= 0)
        throw new RuntimeException("Table column count must be positive.");

    // Pandoc est beaucoup plus naze que ce que j'esperais.
    $spaces = "";
    for ($i = 0; $i < $columns; ++$i)
    {
        if ($i + 1 < $columns)
            $spaces .= "\\ ";
        else
            $spaces .= "\\mbox{}";
    }

    $table = explode("\n", $rules[2]);
    $table = array_filter($table, "empty_cell");
    $table = array_values($table);
    if (count($table) < 2)
        throw new RuntimeException("Table requires a header and a separator line.");

    // On colle des espaces dans la ligne des labels.
    $table[0] = str_replace(
        [" | ", "\n| ", " |\n"],
        ["$spaces|$spaces", "\n|$spaces", "$spaces|\n"],
        $table[0]
    );

    $mode = isset($rules[3]) ? strtolower(trim((string)$rules[3])) : "compact";
    if ($mode === "" || $mode === "compact" || $mode === "minimal")
        return (implode("\n", $table));
    if ($mode !== "wide" && $mode !== "full")
        throw new RuntimeException("Unknown Table layout '$mode'. Expected Compact or Wide.");

    $table[1] = table_wide_separator($table[1], $columns, $rules[4] ?? NULL);
    return (implode("\n", $table));
}
