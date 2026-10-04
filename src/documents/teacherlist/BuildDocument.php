<?php

/*
 * TeacherList document renderer.
 *
 * This renderer exists for administrative teacher rosters whose rows are
 * naturally supplied as a Dabsic array.  Keeping the iteration here avoids
 * expanding Row01..RowNN variables in document templates and removes the
 * corresponding artificial row limit.
 *
 * Canonical data source used by Infosphere:
 *
 * [School
 *   [RectorateTeachers
 *     SchoolYear = "2026-2027"
 *     PeriodStart = "01/09/2026"
 *     PeriodEnd = "31/08/2027"
 *     GeneratedDate = "26/09/2026"
 *     [Teachers
 *       [Teacher42
 *         FamilyName = "Dupont"
 *         FirstName = "Alice"
 *         Teachings = "C ; C++"
 *         Formations = "EF1 ; EF2"
 *         Volume = "120 h"
 *         EntryDate = "02/09/2024"
 *       ]
 *     ]
 *   ]
 * ]
 *
 * A top-level Teachers array is also accepted to make the document type
 * reusable outside Infosphere's School context.
 */

function teacher_list_scalar($value, $default = ""): string
{
    if (is_array($value))
        $value = $value[".this"] ?? $default;
    if (is_string($value) || is_numeric($value) || is_bool($value))
        return trim((string)$value);
    return (string)$default;
}

function teacher_list_rows($value): array
{
    if (!is_array($value) || $value === [])
        return [];

    // Accept a single row as well as a list/associative collection of rows.
    foreach (["FamilyName", "FirstName", "Teachings", "Formations", "Volume", "EntryDate"] as $field)
        if (array_key_exists($field, $value))
            return [$value];

    $rows = [];
    foreach ($value as $row)
        if (is_array($row))
            $rows[] = $row;
    return $rows;
}

function teacher_list_latex($value): string
{
    return LatexEscape(teacher_list_scalar($value));
}

function teacher_list_latex_labels($value): string
{
    $value = teacher_list_scalar($value);
    if ($value === "")
        return "";
    $parts = preg_split('/\s*;\s*/u', $value);
    if (!is_array($parts))
        return teacher_list_latex($value);
    $parts = array_values(array_filter(array_map('trim', $parts), 'strlen'));
    return implode(" ;\\allowbreak ", array_map('LatexEscape', $parts));
}


function teacher_list_split_labels($value): array
{
    $value = teacher_list_scalar($value);
    if ($value === "")
        return [];
    $parts = preg_split('/\s*;\s*/u', $value);
    if (!is_array($parts))
        return [$value];
    $parts = array_map(static function($part) {
        $part = trim((string)$part);
        $part = preg_replace('/\s+/u', ' ', $part);
        return $part;
    }, $parts);
    return array_values(array_filter($parts, 'strlen'));
}

function teacher_list_unique_ordered(array $labels): array
{
    $out = [];
    $seen = [];
    foreach ($labels as $label)
    {
        $label = trim((string)$label);
        if ($label === "")
            continue;
        $key = function_exists('mb_strtolower') ? mb_strtolower($label, 'UTF-8') : strtolower($label);
        if (isset($seen[$key]))
            continue;
        $seen[$key] = true;
        $out[] = $label;
    }
    return $out;
}

function teacher_list_teaching_labels($value): array
{
    return teacher_list_unique_ordered(teacher_list_split_labels($value));
}

function teacher_list_formation_labels($value): array
{
    return teacher_list_unique_ordered(teacher_list_split_labels($value));
}

function teacher_list_latex_lines(array $labels): string
{
    $labels = teacher_list_unique_ordered($labels);
    if ($labels === [])
        return LatexEscape('À renseigner');
    return implode(' \\\\ ', array_map('LatexEscape', $labels));
}

function teacher_list_latex_line_box(array $labels): string
{
    return '\parbox[t]{\linewidth}{\raggedright '.teacher_list_latex_lines($labels).'}';
}

function teacher_list_latex_multicolumn_cell(array $labels, int $columns = 3): string
{
    $labels = teacher_list_unique_ordered($labels);
    if ($labels === [])
        return LatexEscape('À renseigner');
    $columns = max(1, min(4, $columns, count($labels)));
    $rows_per_column = (int)ceil(count($labels) / $columns);
    $chunks = array_chunk($labels, $rows_per_column);
    while (count($chunks) < $columns)
        $chunks[] = [];
    $cells = [];
    foreach ($chunks as $chunk)
    {
        $content = $chunk === [] ? '' : implode(' \\\\ ', array_map('LatexEscape', $chunk));
        $cells[] = '\parbox[t]{0.31\linewidth}{\raggedright '.$content.'}';
    }
    return '\parbox[t]{\linewidth}{\raggedright '.implode('\hfill ', $cells).'}';
}

function teacher_list_config(array $conf): array
{
    return isset($conf["TeacherList"]) && is_array($conf["TeacherList"])
        ? $conf["TeacherList"] : [];
}

function teacher_list_school(array $conf): array
{
    return isset($conf["School"]) && is_array($conf["School"])
        ? $conf["School"] : [];
}

function teacher_list_report(array $conf): array
{
    if (isset($conf["Report"]) && is_array($conf["Report"]))
        return $conf["Report"];
    $school = teacher_list_school($conf);
    return isset($school["RectorateTeachers"]) && is_array($school["RectorateTeachers"])
        ? $school["RectorateTeachers"] : [];
}

function teacher_list_data_rows(array $conf): array
{
    if (isset($conf["Teachers"]) && is_array($conf["Teachers"]))
        return teacher_list_rows($conf["Teachers"]);
    $report = teacher_list_report($conf);
    return teacher_list_rows($report["Teachers"] ?? []);
}

function teacher_list_header(array $conf): void
{
    if (isset($conf["Header"]) && is_string($conf["Header"]))
    {
        echo "\\fancyhead[C]{\\footnotesize ".$conf["Header"]."}\n";
        return;
    }
    if (!isset($conf["Header"]) || !is_array($conf["Header"]))
        return;

    $left = $conf["Header"]["Left"] ?? null;
    $center = $conf["Header"]["Center"] ?? null;
    $right = $conf["Header"]["Right"] ?? null;

    if ($center === null && $left !== null)
    {
        $center = $left;
        $left = null;
    }

    if ($left !== null)
        echo "\\fancyhead[L]{\\footnotesize ".$left."}\n";
    if ($center !== null)
        echo "\\fancyhead[C]{\\footnotesize ".$center."}\n";
    if ($right !== null)
        echo "\\fancyhead[R]{".$right."}\n";
}

function teacher_list_footer(array $conf): void
{
    if (isset($conf["Footer"]) && is_string($conf["Footer"]))
    {
        echo "\\fancyfoot[L]{%\n";
        echo "  \\begin{minipage}[t]{\\textwidth}\\footnotesize\\raggedright ".$conf["Footer"]."\\end{minipage}%\n";
        echo "}\n";
        return;
    }
    if (!isset($conf["Footer"]) || !is_array($conf["Footer"]))
        return;
    foreach (["Left" => "L", "Center" => "C", "Right" => "R"] as $key => $position)
        if (isset($conf["Footer"][$key]))
            echo "\\fancyfoot[".$position."]{".$conf["Footer"][$key]."}\n";
}

function teacher_list_latex_status($value): string
{
    $value = trim(teacher_list_scalar($value));
    if (in_array($value, ['À renseigner', 'A renseigner', 'à renseigner', 'a renseigner'], true))
        return '\textcolor{red}{\textbf{À RENSEIGNER}}';
    if (in_array($value, ['À vérifier', 'A vérifier', 'À verifier', 'A verifier', 'à vérifier', 'a vérifier', 'à verifier', 'a verifier'], true))
        return '\textcolor{red}{\textbf{À VÉRIFIER}}';
    return teacher_list_latex($value);
}

function teacher_list_metadata_line(string $label, $value): void
{
    $value = teacher_list_scalar($value);
    if ($value === "")
        return;
    echo "\\textbf{".LatexEscape($label)."} ".LatexEscape($value)."\\\\\n";
}

function teacher_list_warning(string $label, int $count, string $suffix): void
{
    if ($count <= 0)
        return;
    echo "\\textbf{".LatexEscape($label)."} ".$count." ".LatexEscape($suffix)."\\par\n";
}

function BuildDocument(&$conf)
{
    $conf[".Engine"] = "latex";
    $extra = isset($conf[".LatexExtraHeader"]) && is_string($conf[".LatexExtraHeader"])
        ? rtrim($conf[".LatexExtraHeader"])."\n" : "";
    if (strpos($extra, "futura.ttf") === false)
        $extra .= "\\setmainfont[Path=/usr/share/docbuilder/res/,".
            "BoldFont=futura.ttf,ItalicFont=futura.ttf,BoldItalicFont=futura.ttf]{futura.ttf}\n";
    $extra .= <<<'LATEX'
\usepackage{graphicx}
\usepackage{fancyhdr}
\usepackage{longtable}
\usepackage{array}
\usepackage{xcolor}
LATEX;
    $conf[".LatexExtraHeader"] = $extra."\n";

    $school = teacher_list_school($conf);
    $report = teacher_list_report($conf);
    $settings = teacher_list_config($conf);
    $rows = teacher_list_data_rows($conf);

    $heading = teacher_list_scalar(
        $settings["Heading"] ?? "Liste annuelle des personnes exerçant des fonctions d’enseignement"
    );
    $introduction = teacher_list_scalar(
        $settings["Introduction"] ?? "Cette liste est établie pour la transmission annuelle au rectorat des personnes exerçant des fonctions d’enseignement dans l’établissement."
    );
    $legal_reference = teacher_list_scalar(
        $settings["LegalReference"] ?? "article R. 913-27 du code de l’éducation"
    );
    $volume_note = teacher_list_scalar(
        $settings["VolumeNote"] ?? "Le volume horaire est calculé à partir des sessions planifiées pour l’année scolaire indiquée et doit être vérifié si la planification n’est pas complète."
    );
    $attachments_note = teacher_list_scalar(
        $settings["AttachmentsNote"] ?? "Les justificatifs permettant d’établir que chaque personne remplit les conditions requises pour enseigner sont à joindre séparément à la transmission annuelle. Le programme des cours fait également l’objet de la transmission prévue pour les établissements d’enseignement supérieur privés."
    );

?>
---
geometry: left=1.2cm, right=1.2cm, top=2.3cm, bottom=2.2cm, includehead, includefoot, paperwidth=29.7cm, paperheight=21cm
output: pdf_document
lang: fr-FR
documentclass: article
indent: 0pt
pdf-engine: xelatex
---
```{=latex}
\pagestyle{fancy}
\fancyhf{}
\setlength{\headheight}{0.90cm}
\setlength{\headsep}{0.08cm}
\setlength{\footskip}{0.95cm}
\setlength{\parindent}{0pt}
\renewcommand{\headrulewidth}{0pt}
\renewcommand{\footrulewidth}{0.35pt}
<?php teacher_list_header($conf); ?>
<?php teacher_list_footer($conf); ?>

\begin{center}
{\large\bfseries <?=LatexEscape($heading); ?>}\par
\vspace{0.05cm}
\rule{9.6cm}{0.40pt}
\end{center}
\vspace{0.08cm}

<?php teacher_list_metadata_line("Établissement :", $school["Name"] ?? ($school["LegalName"] ?? "")); ?>
<?php teacher_list_metadata_line("UAI :", $school["UAI"] ?? ($school["Uai"] ?? "")); ?>
<?php teacher_list_metadata_line("Adresse :", $school["Address"] ?? ($school["TrainingAddress"] ?? "")); ?>
<?php teacher_list_metadata_line("Année scolaire :", $report["SchoolYear"] ?? ""); ?>
<?php
$period_start = teacher_list_scalar($report["PeriodStart"] ?? "");
$period_end = teacher_list_scalar($report["PeriodEnd"] ?? "");
if ($period_start !== "" || $period_end !== "")
    teacher_list_metadata_line("Période prise en compte pour le volume horaire :", trim("du ".$period_start." au ".$period_end));
?>
<?php teacher_list_metadata_line("Date d’édition :", $report["GeneratedDate"] ?? ""); ?>

\vspace{0.08cm}

<?php if ($rows === []) { ?>
\textbf{Aucun enseignant n’est actuellement rattaché à cet établissement avec le rôle TEACHER dans user\_school.}\par
<?php } else { ?>
{\scriptsize
\setlength{\tabcolsep}{2.2pt}
\renewcommand{\arraystretch}{1.05}
\begin{longtable}{|>{\raggedright\arraybackslash}p{2.80cm}|>{\raggedright\arraybackslash}p{2.30cm}|>{\raggedright\arraybackslash}p{11.60cm}|>{\raggedright\arraybackslash}p{4.20cm}|>{\raggedleft\arraybackslash}p{1.60cm}|>{\raggedright\arraybackslash}p{2.70cm}|}
\hline
\textbf{Nom} & \textbf{Prénom(s)} & \textbf{Enseignement(s) dispensé(s)} & \textbf{Formation(s) concernée(s)} & \textbf{Volume horaire} & \textbf{Entrée en fonctions} \\
\hline
\endfirsthead
\hline
\textbf{Nom} & \textbf{Prénom(s)} & \textbf{Enseignement(s) dispensé(s)} & \textbf{Formation(s) concernée(s)} & \textbf{Volume horaire} & \textbf{Entrée en fonctions} \\
\hline
\endhead
<?php foreach ($rows as $row) { ?>
<?=teacher_list_latex($row["FamilyName"] ?? ""); ?> & <?=teacher_list_latex($row["FirstName"] ?? ""); ?> & <?=teacher_list_latex_multicolumn_cell(teacher_list_teaching_labels($row["Teachings"] ?? ""), 3); ?> & <?=teacher_list_latex_line_box(teacher_list_formation_labels($row["Formations"] ?? "")); ?> & <?=teacher_list_latex_status($row["Volume"] ?? ""); ?> & <?=teacher_list_latex_status($row["EntryDate"] ?? ""); ?> \\
\hline
<?php } ?>
\end{longtable}
}
<?php } ?>

```
<?php
}
