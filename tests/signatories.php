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

// Legacy Signatories can still provide document-specific metadata while a
// generic signatory provides the actual identity.
$conf = [
    "Signatures" => ["Student" => ["Required" => 1, "Role" => "Bénéficiaire"]],
    "Signatories" => ["Student" => ["Role" => "Ancien libellé"]],
    "Person" => ["Identity" => "Charlie", "Signatory" => 1, "As" => "Student"]
];
ResolveSignatories($conf);
assert($conf["Signatories"]["Student"]["Identity"] === "Charlie");
assert($conf["Signatories"]["Student"]["Role"] === "Bénéficiaire");

expect_exception(function () {
    $conf = ["Signatures" => ["Student" => ["Required" => 1]]];
    ResolveSignatories($conf);
});

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
