<?php
require_once (__DIR__."/../src/tools/Fingerprint.php");

$a = [
    "Document" => "generic",
    "B" => ["z" => 2, "a" => 1],
    "List" => ["b", "a"],
    "DocBuilder" => ["DabsicHash" => "fake"],
];
$b = [
    "List" => ["b", "a"],
    "B" => ["a" => 1, "z" => 2],
    "Document" => "generic",
    "DocBuilder" => ["DabsicHash" => "another-fake"],
];

assert(DocBuilderConfigurationHash($a) === DocBuilderConfigurationHash($b));
$b["List"] = ["a", "b"];
assert(DocBuilderConfigurationHash($a) !== DocBuilderConfigurationHash($b));

echo "fingerprint tests: ok\n";
