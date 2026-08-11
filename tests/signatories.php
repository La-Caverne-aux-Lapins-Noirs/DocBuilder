<?php
require_once (__DIR__."/../src/tools/Signatories.php");

function expect_error(callable $fn, $needle)
{
    try { $fn(); }
    catch (RuntimeException $e) {
        if (strpos($e->getMessage(), $needle) !== false)
            return;
        fwrite(STDERR, "Unexpected error: ".$e->getMessage()."\n");
        exit(1);
    }
    fwrite(STDERR, "Expected error containing '$needle'.\n");
    exit(1);
}

$conf = [
    "Signatures" => ["Student" => ["Required" => 1]],
    "Identity" => ["Identity" => "Alice", "Signatory" => 1, "As" => "Student"]
];
ResolveSignatories($conf);
assert($conf["Signatories"]["Student"]["Identity"] === "Alice");

$conf = [
    "Signatures" => [
        "Director" => ["Required" => 1],
        "Finance" => ["Required" => 1]
    ],
    "Identity" => ["Identity" => "Bob", "Signatory" => 1, "As" => ["Director", "Finance"]]
];
ResolveSignatories($conf);
assert($conf["Signatories"]["Director"]["Identity"] === "Bob");
assert($conf["Signatories"]["Finance"]["Identity"] === "Bob");

expect_error(function () {
    $conf = ["Signatures" => ["Student" => ["Required" => 1]]];
    ResolveSignatories($conf);
}, "Missing required signatory");

expect_error(function () {
    $conf = [
        "Signatures" => ["Student" => ["Required" => 1]],
        "A" => ["Signatory" => 1, "As" => "Student", "Identity" => "A"],
        "B" => ["Signatory" => 1, "As" => "Student", "Identity" => "B"]
    ];
    ResolveSignatories($conf);
}, "Several different signatories");

expect_error(function () {
    $conf = [
        "Signatures" => ["Student" => ["Required" => 1]],
        "A" => ["Signatory" => 1, "As" => "Bad-Role"]
    ];
    ResolveSignatories($conf);
}, "Invalid signatory role");

echo "signatories: OK\n";
