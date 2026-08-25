<?php
require_once (__DIR__."/../src/engines/latex/Table.php");

$body = "| A | B |\n|:--|--:|\n| alpha | beta |";
$compact = Table(["Table", 2, $body]);
if (strpos($compact, str_repeat("-", 40)) !== false)
    throw new RuntimeException("Compact table unexpectedly received wide widths.");

$wide = Table(["Table", 2, $body, "Wide", "60,40"]);
$lines = explode("\n", $wide);
if (!isset($lines[1]) || substr_count($lines[1], "-") < 90)
    throw new RuntimeException("Wide table did not generate an explicit-width separator.");
if (!str_contains($lines[1], ":") || !str_ends_with(trim(explode("|", trim($lines[1]))[2] ?? ""), ":"))
    throw new RuntimeException("Table alignment markers were not preserved.");

$failed = false;
try { Table(["Table", 2, $body, "Wide", "100"]); }
catch (RuntimeException $e) { $failed = true; }
if (!$failed)
    throw new RuntimeException("Invalid proportions should fail.");

echo "table: OK\n";
