<?php

require_once (__DIR__."/../src/tools/Signatories.php");

function expect_exception(callable $fn)
{
    try { $fn(); }
    catch (RuntimeException $e) { return; }
    throw new RuntimeException("Expected exception was not thrown.");
}

$conf = [
    "Signatures" => ["Student" => ["Required" => 1, "Role" => "Etudiant(e)"]],
    "Identity" => ["Identity" => "Alice", "Signatory" => 1, "As" => "Student"]
];
ResolveSignatories($conf);
assert($conf["Signatories"]["Student"]["Identity"] === "Alice");
assert($conf["Signatories"]["Student"]["Role"] === "Etudiant(e)");
assert(!isset($conf["Signatories"]["Student"]["Required"]));
assert(!isset($conf["Signatories"]["Student"]["Signatory"]));
assert(!isset($conf["Signatories"]["Student"]["As"]));

$conf = [
    "Signatures" => ["Director" => ["Required" => 1], "Finance" => ["Required" => 1]],
    "Identity" => ["Identity" => "Bob", "Signatory" => 1, "As" => ["Director", "Finance"]]
];
ResolveSignatories($conf);
assert($conf["Signatories"]["Director"]["Identity"] === "Bob");
assert($conf["Signatories"]["Finance"]["Identity"] === "Bob");

expect_exception(function () {
    $conf = [
        "Signatories" => ["Student" => ["Identity" => "Legacy"]]
    ];
    ResolveSignatories($conf);
});

expect_exception(function () {
    $conf = ["Signatures" => ["Student" => ["Required" => 1]]];
    ResolveSignatories($conf);
});

$conf = [
    "Signatures" => [
        "Director" => ["Required" => 1, "Role" => "Direction"],
        "Student" => ["Required" => 1, "Role" => "Etudiant(e)"],
        "Optional" => ["Required" => 0, "Role" => "Temoin"]
    ]
];
ResolveSignatories($conf, true);
assert(isset($conf["Signatories"]["Director"]));
assert($conf["Signatories"]["Director"]["Role"] === "Direction");
assert(!isset($conf["Signatories"]["Director"]["Required"]));
assert(isset($conf["Signatories"]["Student"]));
assert($conf["Signatories"]["Student"]["Role"] === "Etudiant(e)");
assert(isset($conf["Signatories"]["Optional"]));
assert($conf["Signatories"]["Optional"]["Role"] === "Temoin");

$conf = [
    "Signatures" => [
        "Director" => ["Required" => 1, "Role" => "Direction"],
        "Student" => ["Required" => 1, "Role" => "Etudiant(e)"]
    ],
    "Person" => [
        "Identity" => "Alice",
        "Signatory" => 1,
        "As" => "Director"
    ]
];
ResolveSignatories($conf, true);
assert($conf["Signatories"]["Director"]["Identity"] === "Alice");
assert($conf["Signatories"]["Director"]["Role"] === "Direction");
assert(!isset($conf["Signatories"]["Student"]["Identity"]));
assert($conf["Signatories"]["Student"]["Role"] === "Etudiant(e)");

expect_exception(function () {
    $conf = [
        "Signatures" => ["Student" => ["Required" => 1]],
        "A" => ["Signatory" => 1, "As" => "Student", "Identity" => "A"],
        "B" => ["Signatory" => 1, "As" => "Student", "Identity" => "B"]
    ];
    ResolveSignatories($conf);
});

expect_exception(function () {
    $conf = [
        "Signatures" => ["Student" => ["Required" => 1]],
        "A" => ["Signatory" => 1, "As" => "Bad-Role"]
    ];
    ResolveSignatories($conf);
});

echo "signatories: OK\n";
