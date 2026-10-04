<?php
require_once (__DIR__."/../src/tools/LatexEscape.php");
require_once (__DIR__."/../src/documents/teacherlist/BuildDocument.php");

function teacher_list_test_render(array $configuration): array
{
    ob_start();
    BuildDocument($configuration);
    return [$configuration, ob_get_clean()];
}

$conf = [
    "Header" => ["Left" => "Liste rectorat"],
    "Footer" => "EFRITS — pied de page",
    "School" => [
        "Name" => "L’EFRITS",
        "UAI" => "0940000X",
        "Address" => "32a Avenue Pierre Sémard 94200 Ivry-sur-Seine",
        "RectorateTeachers" => [
            "SchoolYear" => "2026-2027",
            "PeriodStart" => "01/09/2026",
            "PeriodEnd" => "31/08/2027",
            "GeneratedDate" => "26/09/2026",
            "MissingEntryDateCount" => 1,
            "MissingTeachingCount" => 0,
            "UnverifiedVolumeCount" => 0,
            "Teachers" => [
                "Teacher10" => [
                    "FamilyName" => "Alpha & Associés",
                    "FirstName" => "Alice",
                    "Teachings" => "Programmation graphique ; Système & Linux ; Programmation graphique",
                    "Formations" => "EF1 ; EF2",
                    "Volume" => "12,5 h",
                    "EntryDate" => "02/09/2024",
                ],
                "Teacher20" => [
                    "FamilyName" => "Beta",
                    "FirstName" => "Bob",
                    "Teachings" => "Réseau",
                    "Formations" => "EF3",
                    "Volume" => "À vérifier",
                    "EntryDate" => "À renseigner",
                ],
            ],
        ],
    ],
    "Director" => ["Identity" => "Jason Exemple"],
];

[$rendered_conf, $body] = teacher_list_test_render($conf);
if (($rendered_conf[".Engine"] ?? "") !== "latex")
    throw new RuntimeException("TeacherList did not select the latex engine.");
if (strpos($rendered_conf[".LatexExtraHeader"] ?? "", "longtable") === false)
    throw new RuntimeException("TeacherList did not request longtable support.");
if (strpos($rendered_conf[".LatexExtraHeader"] ?? "", "\\usepackage{graphicx}") === false)
    throw new RuntimeException("TeacherList did not request graphicx support for header/footer images.");
if (strpos($rendered_conf[".LatexExtraHeader"] ?? "", "futura.ttf") === false)
    throw new RuntimeException("TeacherList did not select the bundled Futura font.");
if (substr_count($body, "\\hline") < 4 || strpos($body, "\\begin{longtable}") === false)
    throw new RuntimeException("TeacherList did not render a real LaTeX table.");
if (strpos($body, "Alpha \\& Associés") === false || strpos($body, "Système \\& Linux") === false)
    throw new RuntimeException("TeacherList did not LaTeX-escape row data.");
if (strpos($body, "paperwidth=29.7cm, paperheight=21cm") === false)
    throw new RuntimeException("TeacherList did not switch to landscape orientation.");
if (substr_count($body, "Programmation graphique") !== 1)
    throw new RuntimeException("TeacherList did not deduplicate teaching labels.");
if (substr_count($body, "\\parbox[t]{0.31\\linewidth}") < 3)
    throw new RuntimeException("TeacherList did not render teachings as a multi-column cell.");
if (strpos($body, "EF1 \\\\ EF2") === false)
    throw new RuntimeException("TeacherList did not render formations one per line.");
if (strpos($body, "Row01") !== false || strpos($body, "|:--") !== false || strpos($body, "[@Table") !== false)
    throw new RuntimeException("TeacherList fell back to the legacy expanded Markdown table.");
if (strpos($body, "Cette liste est établie") !== false || strpos($body, "À compléter avant transmission") !== false || strpos($body, "Jason Exemple") !== false)
    throw new RuntimeException("TeacherList did not remove optional explanatory text.");
if (strpos($body, '\\textcolor{red}{\\textbf{À RENSEIGNER}}') === false || strpos($body, '\\textcolor{red}{\\textbf{À VÉRIFIER}}') === false)
    throw new RuntimeException("TeacherList did not highlight status values.");

$empty = $conf;
$empty["School"]["RectorateTeachers"]["Teachers"] = [];
[, $empty_body] = teacher_list_test_render($empty);
if (strpos($empty_body, "Aucun enseignant") === false || strpos($empty_body, "\\begin{longtable}") !== false)
    throw new RuntimeException("TeacherList empty-state rendering failed.");

echo "teacher_list: OK\n";
