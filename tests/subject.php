<?php

require_once (__DIR__."/../src/documents/subject/BuildDocument.php");

$conf = [
    "Language" => "FR",
    "Revision" => "4.0",
    "Author" => "Auteur",
    "School" => [
        "Name" => "École de test",
        "DocumentLogo" => "school.png",
    ],
    "Matter" => ["FR" => "Matière dynamique"],
    "Activity" => [
        "FR" => "Activité dynamique",
        "Description" => ["FR" => "Description dynamique"],
        "Logo" => "activity.png",
    ],
    "Laboratory" => [
        "Logo" => "laboratory.png",
        "SmallLogo" => "laboratory-small.png",
    ],
    "Exercises" => [],
];

ob_start();
BuildDocument($conf);
$out = ob_get_clean();
assert(str_contains($out, "École de test"));
assert(str_contains($out, "school.png"));
assert(str_contains($out, "laboratory.png"));
assert(str_contains($out, "laboratory-small.png"));
assert(str_contains($out, "Activité dynamique"));
assert(str_contains($out, "Description dynamique"));
assert(str_contains($out, "Révision 4.0"));
assert(str_contains($out, "Auteur : Auteur"));

$conf["FrontPage"] = [
    "Message" => ["FR" => "Titre libre"],
    "Description" => ["FR" => "Description libre"],
    "Logo" => "custom.png",
    "SchoolLogo" => "custom-school.png",
];
ob_start();
BuildDocument($conf);
$out = ob_get_clean();
assert(str_contains($out, "Titre libre"));
assert(str_contains($out, "Description libre"));
assert(str_contains($out, "custom.png"));
assert(str_contains($out, "custom-school.png"));

assert(subject_document_localized(["EN" => "English"], "EN") === "English");
assert(subject_document_localized("Literal", "FR") === "Literal");

echo "subject tests: ok\n";

$resourceDir = sys_get_temp_dir()."/docbuilder-subject-".getmypid();
@mkdir($resourceDir, 0700, true);
file_put_contents($resourceDir."/theme.png", "not-an-image");
$conf = [
    "Language" => "FR",
    ".IncludePaths" => [$resourceDir],
    "FrontPage" => [
        "CoverImage" => "theme.png",
        "ExerciseList" => 1,
        "NumberSections" => 1,
    ],
    "Exercises" => [
        [
            "Name" => [".this" => "test", "FR" => "Exercice thématique"],
            "Document" => ["Description" => ["FR" => "Texte supérieur"]],
        ],
    ],
    "SubjectLayout" => [
        "test" => [
            "PageModel" => "HorizontalSplit",
            "TopBlock" => [
                "Image" => "theme.png",
                "ImageAlign" => "right",
            ],
            "BottomBlock" => [
                "Text" => ["FR" => "Texte inférieur"],
                "Image" => "theme.png",
                "ImageAlign" => "left",
                "TextStyle" => "italic",
            ],
        ],
    ],
];
ob_start();
BuildDocument($conf);
$out = ob_get_clean();
assert(str_contains($out, "01 - Exercice thématique"));
assert(str_contains($out, "01 – Exercice thématique"));
assert(str_contains($out, "Texte supérieur"));
assert(str_contains($out, "Texte inférieur"));
assert(substr_count($out, "\\begin{wrapfigure}") === 2);
assert(str_contains($out, "\\begin{wrapfigure}{r}"));
assert(str_contains($out, "\\begin{wrapfigure}{l}"));
assert(str_contains($out, "\\hrule"));
assert(str_contains($out, realpath($resourceDir."/theme.png")));
assert(str_contains($conf[".LatexExtraHeader"], "wrapfig"));
assert(str_contains($conf[".LatexExtraHeader"], "placeins"));
@unlink($resourceDir."/theme.png");
@rmdir($resourceDir);
