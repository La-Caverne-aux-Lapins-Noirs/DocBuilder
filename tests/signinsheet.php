<?php
require_once (__DIR__."/../src/tools/LatexEscape.php");
require_once (__DIR__."/../src/documents/signinsheet/BuildDocument.php");

function signinsheet_expect($condition, $message)
{
    if (!$condition)
        throw new RuntimeException("sign-in sheet test failed: ".$message);
}

$signature = tempnam(sys_get_temp_dir(), "docbuilder-signinsheet-");
file_put_contents($signature, "signature");

$conf = [
    "Header" => ["Left" => "TITLE", "Right" => "LOGO"],
    "Footer" => "LEGAL FOOTER",
    "SignInSheetLayout" => ["PeoplePerPage" => 30, "TrainerInlineLimit" => 8],
    "Sheets" => [[
        "SchoolName" => "EFRITS",
        "CycleName" => "EF3",
        "ActivityName" => "Systèmes",
        "DateTime" => "26/09/2026 09:00 - 17:00",
        "Address" => "32a Avenue Pierre Sémard, Ivry-sur-Seine",
        "Rooms" => "Dorothy Vaughan",
        "Duration" => "7 h 00",
        "SessionId" => 42,
        "Morning" => "09:00-13:00",
        "Afternoon" => "sans objet",
        "MorningApplicable" => 1,
        "AfternoonApplicable" => 0,
        "Capacity" => 4,
        "Students" => [
            ["Identity" => "Alice Exemple"],
            ["Identity" => "Bob Exemple"],
        ],
        "Trainers" => [
            ["Identity" => "Charlie Formateur", "Present" => 1, "Signature" => $signature],
            ["Identity" => "Dana Sans Signature", "Present" => 1, "Signature" => ""],
            ["Identity" => "Eve Non Présente", "Present" => 0, "Signature" => $signature],
        ],
    ]],
];

ob_start();
BuildDocument($conf);
$out = ob_get_clean();

signinsheet_expect(is_file(__DIR__."/../src/documents/signinsheet/configuration.tex"), "LaTeX configuration is packaged");
signinsheet_expect($conf[".Engine"] === "latex", "LaTeX engine selected");
signinsheet_expect(strpos($conf[".LatexExtraHeader"], "futura.ttf") !== false, "Futura is selected");
signinsheet_expect(strpos($out, "\\fancyhead[L]{TITLE}") !== false, "usual left document title header");
signinsheet_expect(strpos($out, "\\fancyhead[R]{\\raisebox{-0.35cm}{LOGO}}") !== false, "usual right logo header");
signinsheet_expect(strpos($out, "LEGAL FOOTER") !== false, "legal footer rendered");
signinsheet_expect(strpos($out, "Formateurs") !== false, "trainer section has concise title");
signinsheet_expect(strpos($out, "Formateurs ayant déclaré leur présence") === false, "old trainer title removed");
signinsheet_expect(strpos($out, "Salle non renseignée") === false, "no invented room fallback");
signinsheet_expect(strpos($out, "Alice Exemple") !== false && strpos($out, "Charlie Formateur") !== false, "people rendered");
signinsheet_expect(substr_count($out, $signature) === 1, "stored trainer signature rendered only in the applicable half-day");
signinsheet_expect(strpos($out, "\\fontsize{5.8}{6.2}\\selectfont Présent") !== false, "present fallback rendered when no signature exists");
signinsheet_expect(strpos($out, "session \#42 -- page 1/1") !== false, "session/page reference rendered");

@unlink($signature);
echo "signinsheet tests: ok\n";
