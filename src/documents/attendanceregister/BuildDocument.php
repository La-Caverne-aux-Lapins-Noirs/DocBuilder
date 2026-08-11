<?php

function attendance_register_text($value): string
{
    if (is_array($value))
        $value = $value[".this"] ?? "";
    return is_scalar($value) ? trim((string)$value) : "";
}

function attendance_register_person_name(array $person): string
{
    $identity = attendance_register_text($person["Identity"] ?? "");
    if ($identity !== "")
        return $identity;

    $firstName = attendance_register_text($person["FirstName"] ?? "");
    $lastName = attendance_register_text($person["UseName"] ?? ($person["Name"] ?? ""));
    return trim($firstName." ".$lastName);
}

function attendance_register_date($value, string $field): DateTimeImmutable
{
    $value = attendance_register_text($value);
    foreach (["!Y-m-d", "!d/m/Y"] as $format)
    {
        $date = DateTimeImmutable::createFromFormat($format, $value);
        $errors = DateTimeImmutable::getLastErrors();
        if ($date !== false && ($errors === false || ($errors["warning_count"] === 0 && $errors["error_count"] === 0)))
            return $date;
    }
    throw new RuntimeException("AttendanceRegister: invalid or missing ".$field." '".$value."'.");
}

function attendance_register_list($value): array
{
    if (!is_array($value) || $value === [])
        return [];
    // Si on est en php8
    if (function_exists("array_is_list") && array_is_list($value))
        return $value;
    if (array_keys($value) === range(0, count($value) - 1))
        return $value;
    return [$value];
}

function attendance_register_true($value): bool
{
    if (is_bool($value))
        return $value;
    return in_array(strtolower(attendance_register_text($value)), ["1", "true", "yes", "oui"], true);
}

function attendance_register_normalize_status(string $status): string
{
    $status = strtolower(strtr(trim($status), [
        "é" => "e", "è" => "e", "ê" => "e", "ë" => "e",
        "à" => "a", "â" => "a", "ä" => "a",
        "î" => "i", "ï" => "i",
        "ô" => "o", "ö" => "o",
        "ù" => "u", "û" => "u", "ü" => "u",
        "ç" => "c",
    ]));
    return $status;
}

function attendance_register_marker($value): string
{
    if (is_array($value))
    {
        if (isset($value["Marker"]))
            return strtoupper(substr(attendance_register_text($value["Marker"]), 0, 3));
        if (attendance_register_true($value["Late"] ?? false))
            return "R";
        $value = $value["Status"] ?? ($value[".this"] ?? "");
    }
    if (is_bool($value))
        return $value ? "P" : "A";

    $status = attendance_register_normalize_status(attendance_register_text($value));
    if (in_array($status, ["p", "present", "presence"], true))
        return "P";
    if (in_array($status, ["a", "absent", "absence"], true))
        return "A";
    if (in_array($status, ["r", "late", "retard"], true))
        return "R";
    return $status === "" ? "" : strtoupper(substr($status, 0, 3));
}

function attendance_register_activities(array $day): array
{
    return attendance_register_list($day["Activities"] ?? []);
}

function attendance_register_status_color(string $marker): string
{
    if ($marker === "P")
        return "attpresent";
    if ($marker === "R")
        return "attlate";
    if ($marker === "A")
        return "attabsent";
    return "black";
}

function attendance_register_activity_parts(array $day): array
{
    $parts = [];
    foreach (attendance_register_activities($day) as $activity)
    {
        if (!is_array($activity))
        {
            $name = attendance_register_text($activity);
            if ($name !== "")
                $parts[] = ["Text" => $name, "Marker" => ""];
            continue;
        }

        $name = attendance_register_text($activity["Name"] ?? "Activité");
        $typeLabel = attendance_register_text(
            $activity["TypeLabel"] ?? ($activity["Type"] ?? "")
        );
        if ($typeLabel !== "")
            $name = $typeLabel." — ".$name;
        $trainer = attendance_register_text($activity["Trainer"] ?? ($activity["Teacher"] ?? ""));
        if ($trainer !== "")
        {
            $trainerLabel = attendance_register_text($activity["TrainerLabel"] ?? "Formateur");
            $name .= " — ".($trainerLabel !== "" ? $trainerLabel : "Formateur")." : ".$trainer;
        }
        $marker = attendance_register_marker($activity);
        $declaration = attendance_register_text(
            $activity["Declaration"] ?? ($activity["DeclaredAt"] ?? "")
        );
        $lateDuration = attendance_register_text(
            $activity["LateDuration"] ?? ($activity["Delay"] ?? "")
        );
        $conclusion = $marker;
        if ($marker === "P" && $declaration !== "")
            $conclusion .= "@".$declaration;
        else if ($marker === "R" && $lateDuration !== "")
            $conclusion .= " ".$lateDuration;
        if ($conclusion !== "")
            $name .= " [".$conclusion."]";
        $parts[] = ["Text" => $name, "Marker" => $marker];
    }
    return $parts;
}

function attendance_register_render_activities(array $activities): void
{
    foreach ($activities as $index => $activity)
    {
        if ($index > 0)
            echo " ; ";
        $marker = attendance_register_text($activity["Marker"] ?? "");
        $text = attendance_register_text($activity["Text"] ?? "");
        echo "\\attactivity{".attendance_register_status_color($marker)."}{".LatexEscape($text)."}";
    }
}

function attendance_register_index_days($days): array
{
    $indexed = [];
    foreach (attendance_register_list($days) as $day)
    {
        if (!is_array($day) || !isset($day["Date"]))
            continue;
        $date = attendance_register_date($day["Date"], "Days.Date");
        $indexed[$date->format("Y-m-d")] = $day;
    }
    return $indexed;
}

function attendance_register_rows(DateTimeImmutable $start, DateTimeImmutable $end, array $days): array
{
    if ($end < $start)
        throw new RuntimeException("AttendanceRegister: Period.End is before Period.Start.");

    $dayNames = [1 => "Lun.", 2 => "Mar.", 3 => "Mer.", 4 => "Jeu.", 5 => "Ven.", 6 => "Sam.", 7 => "Dim."];
    $rows = [];
    for ($date = $start; $date <= $end; $date = $date->modify("+1 day"))
    {
        $key = $date->format("Y-m-d");
        $day = $days[$key] ?? [];
        $weekday = (int)$date->format("N");
        if ($weekday > 5 && attendance_register_activities($day) === [])
            continue;

        $rows[] = [
            "Week" => $date->format("o-W"),
            "Date" => $dayNames[$weekday]." ".$date->format("d/m"),
            "Weekend" => $weekday > 5,
            "Morning" => attendance_register_marker($day["Morning"] ?? ""),
            "Afternoon" => attendance_register_marker($day["Afternoon"] ?? ""),
            "Activities" => attendance_register_activity_parts($day),
        ];
    }
    return $rows;
}

function attendance_register_split_rows(array $rows): array
{
    $weeks = [];
    foreach ($rows as $row)
        $weeks[$row["Week"]][] = $row;

    $weeks = array_values($weeks);
    $middle = (int)ceil(count($weeks) / 2);
    $columns = [[], []];
    foreach ($weeks as $index => $week)
        $columns[$index < $middle ? 0 : 1] = array_merge($columns[$index < $middle ? 0 : 1], $week);
    return $columns;
}

function attendance_register_address(array $entity): string
{
    $address = attendance_register_text($entity["Address"] ?? "");
    if ($address !== "")
        return $address;

    $street = attendance_register_text($entity["Street"] ?? "");
    $city = trim(attendance_register_text($entity["PostalCode"] ?? "")." ".attendance_register_text($entity["City"] ?? ""));
    return implode(", ", array_filter([$street, $city], "strlen"));
}

function attendance_register_contact_lines(array $entity): array
{
    return array_values(array_filter([
        attendance_register_address($entity),
        attendance_register_text($entity["Phone"] ?? ""),
        attendance_register_text($entity["Mail"] ?? ""),
    ], "strlen"));
}

function attendance_register_labeled_line(string $label, $value): string
{
    $value = attendance_register_text($value);
    return $value === "" ? "" : $label." : ".$value;
}

function attendance_register_school_contact(array $school): string
{
    $contact = is_array($school["Contact"] ?? null) ? $school["Contact"] : [];
    $parts = array_values(array_filter([
        attendance_register_person_name($contact),
        attendance_register_text($contact["Mail"] ?? ""),
        attendance_register_text($contact["Phone"] ?? ""),
    ], "strlen"));
    if ($parts === [])
        $parts = array_values(array_filter([
            attendance_register_text($school["Mail"] ?? ""),
            attendance_register_text($school["Phone"] ?? ""),
        ], "strlen"));
    return implode(" -- ", $parts);
}

function attendance_register_company_name(array $student): string
{
    $company = $student["Company"] ?? null;
    if (is_array($company))
        return attendance_register_text($company["Name"] ?? ($company["Identity"] ?? ""));
    return attendance_register_text($student["CompanyName"] ?? $company ?? "");
}

function attendance_register_duration($value): string
{
    if (is_array($value))
        $value = $value[".this"] ?? 0;
    if (!is_numeric($value))
        return attendance_register_text($value);

    $seconds = max(0, (int)round((float)$value));
    $minutes = (int)round($seconds / 60);
    $hours = intdiv($minutes, 60);
    $minutes %= 60;
    return $minutes === 0
        ? $hours." h"
        : $hours." h ".sprintf("%02d", $minutes);
}

function attendance_register_percentage($value): string
{
    if (is_array($value))
        $value = $value[".this"] ?? 0;
    if (!is_numeric($value))
        return attendance_register_text($value);
    return number_format((float)$value, 2, ",", " ")." %";
}

function attendance_register_total_rows(array $totals): array
{
    return [
        [
            "Durée attendue échue",
            attendance_register_duration($totals["PlannedDuration"] ?? 0),
            "Présence",
            attendance_register_duration($totals["PresenceDuration"] ?? 0),
            "Cumul des retards",
            attendance_register_duration($totals["LateDuration"] ?? 0),
        ],
        [
            "Absences",
            attendance_register_duration($totals["AbsenceDuration"] ?? 0),
            "Absences justifiées",
            attendance_register_duration($totals["JustifiedAbsenceDuration"] ?? 0),
            "Absences injustifiées",
            attendance_register_duration($totals["UnjustifiedAbsenceDuration"] ?? 0),
        ],
    ];
}

function attendance_register_student_file_reference(array $student): string
{
    foreach (["ContractReference", "FileReference", "DossierReference"] as $field)
    {
        $value = attendance_register_text($student[$field] ?? "");
        if ($value !== "")
            return $value;
    }
    return "";
}

function attendance_register_person_key(array $person): string
{
    $id = attendance_register_text($person["Id"] ?? "");
    if ($id !== "")
        return "id:".$id;
    $codename = strtolower(attendance_register_text($person["Codename"] ?? ""));
    if ($codename !== "")
        return "codename:".$codename;
    return "name:".strtolower(attendance_register_person_name($person));
}

function attendance_register_people($value): array
{
    $people = [];
    foreach (attendance_register_list($value) as $person)
    {
        if (!is_array($person) || attendance_register_person_name($person) === "")
            continue;
        $people[attendance_register_person_key($person)] = $person;
    }
    return array_values($people);
}

function attendance_register_person_contact(array $person): array
{
    return array_values(array_filter([
        attendance_register_text($person["Mail"] ?? ($person["Email"] ?? "")),
        attendance_register_text($person["Phone"] ?? ""),
    ], "strlen"));
}

function attendance_register_latex_path($value): string
{
    $path = attendance_register_text($value);
    return str_replace(["{", "}", "\r", "\n"], "", $path);
}

function attendance_register_render_trainer_grid(array $trainers): void
{
    foreach (array_chunk($trainers, 5) as $chunk)
    {
        $columns = count($chunk);
        echo "\\begin{tabularx}{\\textwidth}{|";
        echo str_repeat(">{\\RaggedRight\\arraybackslash}X|", $columns);
        echo "}\n\\hline\n";
        foreach ($chunk as $index => $trainer)
        {
            if ($index > 0)
                echo " & ";
            $name = attendance_register_person_name($trainer);
            $role = attendance_register_text($trainer["Role"] ?? "Formateur");
            $contact = implode("\\\\", array_map("LatexEscape", attendance_register_person_contact($trainer)));
            echo "\\atttrainercell{".LatexEscape($name)."}{".LatexEscape($role)."}{".$contact."}";
        }
        echo " \\\\\\hline\n\\end{tabularx}\n";
    }
}

function attendance_register_render_signature_grid(array $signers): void
{
    foreach (array_chunk($signers, 7) as $chunk)
    {
        $columns = count($chunk);
        echo "\\begin{tabularx}{\\textwidth}{|";
        echo str_repeat(">{\\RaggedRight\\arraybackslash}X|", $columns);
        echo "}\n\\hline\n";
        foreach ($chunk as $index => $signer)
        {
            if ($index > 0)
                echo " & ";
            echo "\\attsignaturecell{".LatexEscape(attendance_register_text($signer["Title"] ?? "Signature"))."}{";
            echo LatexEscape(attendance_register_text($signer["Name"] ?? ""))."}{";
            echo LatexEscape(attendance_register_text($signer["Role"] ?? ""))."}";
        }
        echo " \\\\\\hline\n\\end{tabularx}\n";
    }
}

function attendance_register_render_rows(array $rows): void
{
    foreach ($rows as $row)
    {
        $morning = $row["Morning"];
        $afternoon = $row["Afternoon"];
        echo LatexEscape($row["Date"])." & ";
        echo "\\attmark{".attendance_register_status_color($morning)."}{".LatexEscape($morning)."} & ";
        echo "\\attmark{".attendance_register_status_color($afternoon)."}{".LatexEscape($afternoon)."} & ";
        attendance_register_render_activities($row["Activities"]);
        echo " \\\\\hline\n";
    }
}

function attendance_register_render_sheet(array $conf): void
{
    $period = is_array($conf["Period"] ?? null) ? $conf["Period"] : [];
    $student = is_array($conf["Student"] ?? null) ? $conf["Student"] : [];
    $cycle = is_array($conf["Cycle"] ?? null) ? $conf["Cycle"] : [];
    $director = is_array($conf["CycleDirector"] ?? null) ? $conf["CycleDirector"] : [];
    $school = is_array($conf["School"] ?? null) ? $conf["School"] : [];
    $documentInfo = is_array($conf["DocumentInfo"] ?? null) ? $conf["DocumentInfo"] : [];
    $totals = is_array($conf["Totals"] ?? null) ? $conf["Totals"] : [];
    $trainers = attendance_register_people($conf["Trainers"] ?? []);

    $start = attendance_register_date($period["Start"] ?? "", "Period.Start");
    $end = attendance_register_date($period["End"] ?? "", "Period.End");
    $rows = attendance_register_rows($start, $end, attendance_register_index_days($conf["Days"] ?? []));
    [$leftRows, $rightRows] = attendance_register_split_rows($rows);

    $title = attendance_register_text($conf["Title"] ?? "Feuille d'émargement trimestrielle");
    $studentName = attendance_register_person_name($student);
    $directorName = attendance_register_person_name($director);
    $directorRole = attendance_register_text($director["Role"] ?? "Responsable de formation");
    $schoolName = attendance_register_text($school["Name"] ?? "Structure de formation");
    $schoolLogo = attendance_register_latex_path(
        $school["DocumentLogo"] ?? ($school["Logo"] ?? "")
    );
    $reference = attendance_register_text($conf["Reference"] ?? "");
    $issueDate = attendance_register_text($documentInfo["IssueDate"] ?? "");
    $dataCutoff = attendance_register_text($documentInfo["DataCutoff"] ?? "");
    $documentStatus = attendance_register_text($documentInfo["Status"] ?? "");
    $documentStatusParts = preg_split('/\s*[—–]\s*/u', $documentStatus, 2);
    if (!is_array($documentStatusParts) || $documentStatusParts === [])
        $documentStatusParts = [$documentStatus];

    $periodParts = array_filter([
        attendance_register_text($period["Label"] ?? ""),
        attendance_register_text($period["Cycle"] ?? ""),
    ], "strlen");
    $periodTitle = implode(" -- ", $periodParts);
    if ($periodTitle !== "")
        $periodTitle .= " -- ";
    $periodTitle .= "du ".$start->format("d/m/Y")." au ".$end->format("d/m/Y");
    if ($reference !== "")
        $periodTitle .= " -- Réf. ".$reference;

    $administrativeNumbers = array_values(array_filter([
        attendance_register_labeled_line("SIRET", $school["SIRET"] ?? ""),
        attendance_register_labeled_line("NDA", $school["NDA"] ?? ($school["FormationActivityNumber"] ?? "")),
        attendance_register_labeled_line("UAI", $school["UAI"] ?? ""),
    ], "strlen"));
    $schoolLines = array_values(array_filter([
        attendance_register_labeled_line("Lieu de formation", $school["TrainingAddress"] ?? ($school["SchoolAddress"] ?? "")),
        implode(" -- ", $administrativeNumbers),
        attendance_register_labeled_line("CFA", $school["CFAName"] ?? ""),
        attendance_register_labeled_line("UFA / établissement exécutant", $school["ExecutingEstablishmentName"] ?? ($school["UFAName"] ?? "")),
        attendance_register_labeled_line("Contact établissement", attendance_register_school_contact($school)),
    ], "strlen"));

    $studentLines = [];
    if (attendance_register_text($student["Id"] ?? ($student["StudentId"] ?? "")) !== "")
        $studentLines[] = "N° étudiant : ".attendance_register_text($student["Id"] ?? $student["StudentId"]);

    $currentYear = attendance_register_text(
        $cycle["CurrentYear"] ?? ($student["CurrentYear"] ?? "")
    );
    if ($currentYear !== "")
        $studentLines[] = "Année en cours : ".$currentYear." (EF".$currentYear.")";

    $trimester = attendance_register_text(
        $cycle["Trimester"] ?? ($student["Trimester"] ?? "")
    );
    if ($trimester !== "")
        $studentLines[] = "Trimestre : ".$trimester;

    $promotionYear = attendance_register_text(
        $cycle["PromotionYear"] ?? ($student["PromotionYear"] ?? "")
    );
    if ($promotionYear !== "")
        $studentLines[] = "Promotion : ".$promotionYear;

    if (($companyName = attendance_register_company_name($student)) !== "")
        $studentLines[] = "Entreprise employeur : ".$companyName;
    if (($fileReference = attendance_register_student_file_reference($student)) !== "")
        $studentLines[] = "Référence contrat / dossier : ".$fileReference;
    $institutionalMail = attendance_register_text(
        $student["InstitutionalMail"] ?? ($student["SchoolMail"] ?? "")
    );
    if ($institutionalMail !== "")
        $studentLines[] = $institutionalMail;
    $directorLines = attendance_register_contact_lines($director);
    $totalRows = attendance_register_total_rows($totals);
    $directorKey = attendance_register_person_key($director);
    $directorSignerRole = $directorRole;
    foreach ($trainers as $trainer)
    {
        if (attendance_register_person_key($trainer) !== $directorKey)
            continue;
        $trainerRole = attendance_register_text($trainer["Role"] ?? "Formateur");
        if ($trainerRole !== "" && stripos($directorSignerRole, $trainerRole) === false)
            $directorSignerRole .= " / ".$trainerRole;
        break;
    }
    $signers = [
        ["Title" => "Apprenant", "Name" => $studentName, "Role" => "Apprenant"],
        ["Title" => "Responsable", "Name" => $directorName, "Role" => $directorSignerRole],
    ];
    foreach ($trainers as $trainer)
    {
        if (attendance_register_person_key($trainer) === $directorKey)
            continue;
        $signers[] = [
            "Title" => "Formateur",
            "Name" => attendance_register_person_name($trainer),
            "Role" => attendance_register_text($trainer["Role"] ?? "Formateur"),
        ];
    }
?>
\pagestyle{empty}
\setlength{\parindent}{0pt}
\setlength{\tabcolsep}{1.2pt}
\setlength{\fboxsep}{2.5pt}
\renewcommand{\arraystretch}{0.92}

\noindent
\begin{tabularx}{\textwidth}{@{}p{2.9cm}>{\centering\arraybackslash}X>{\RaggedLeft\arraybackslash}p{3.25cm}@{}}
<?php if ($schoolLogo !== "") { ?>\includegraphics[width=2.8cm,height=1.35cm,keepaspectratio]{\detokenize{<?=$schoolLogo; ?>}}<?php } ?>
&
{\fontsize{14}{15}\selectfont\bfseries <?=LatexEscape($title); ?>}\par
\vspace{0.35mm}
{\fontsize{8.2}{9}\selectfont <?=LatexEscape($periodTitle); ?>}
&
{\fontsize{6.2}{6.8}\selectfont\hyphenpenalty=10000\exhyphenpenalty=10000 <?php if ($documentStatus !== "") { ?><?php foreach ($documentStatusParts as $statusPart) { ?>\textbf{<?=LatexEscape($statusPart); ?>}\par{}<?php } ?><?php } ?><?php if ($issueDate !== "") { ?>Émis le <?=LatexEscape($issueDate); ?>\par{}<?php } ?><?php if ($dataCutoff !== "") { ?>Données arrêtées au <?=LatexEscape($dataCutoff); ?><?php } ?>}
\end{tabularx}
\vspace{0.5mm}

\noindent
\begin{tabularx}{\textwidth}{@{}>{\hsize=1.35\hsize\linewidth=\hsize\RaggedRight\arraybackslash}X@{\hspace{2mm}}>{\hsize=.825\hsize\linewidth=\hsize\RaggedRight\arraybackslash}X@{\hspace{2mm}}>{\hsize=.825\hsize\linewidth=\hsize\RaggedRight\arraybackslash}X@{}}
\attinfobox{Structure de formation}{<?=LatexEscape($schoolName); ?><?php foreach ($schoolLines as $line) { ?>\\<?=LatexEscape($line); ?><?php } ?>}
&
\attinfobox{Apprenant}{<?=LatexEscape($studentName); ?><?php foreach ($studentLines as $line) { ?>\\<?=LatexEscape($line); ?><?php } ?>}
&
\attinfobox{Responsable de formation}{<?=LatexEscape($directorName); ?><?php if ($directorRole !== "" && $directorRole !== "Responsable de formation") { ?>\\<?=LatexEscape($directorRole); ?><?php } ?><?php foreach ($directorLines as $line) { ?>\\<?=LatexEscape($line); ?><?php } ?>}
\end{tabularx}

\vspace{1mm}
\begin{center}
{\fontsize{5.8}{6.4}\selectfont
\begin{minipage}[t]{0.492\textwidth}
\attendancetableheader
<?php attendance_register_render_rows($leftRows); ?>
\end{tabularx}
\end{minipage}\hfill%
\begin{minipage}[t]{0.492\textwidth}
\attendancetableheader
<?php attendance_register_render_rows($rightRows); ?>
\end{tabularx}
\end{minipage}
}
\end{center}

\vspace{0.6mm}
{\fontsize{6.4}{7.1}\selectfont\textbf{Légende :} M : 9 h-13 h ; AM : 14 h-17 h. \attmark{attpresent}{P} présent ; \attmark{attabsent}{A} absent ; \attmark{attlate}{R} retard. @ indique l'heure de déclaration d'une présence ; la durée suivant R indique le retard constaté. Totaux calculés sur les demi-journées échues.}

\vspace{0.6mm}
{\fontsize{6.2}{6.9}\selectfont
\setlength{\tabcolsep}{1.5pt}
\renewcommand{\arraystretch}{0.95}
\begin{tabularx}{\textwidth}{|>{\RaggedRight\arraybackslash}X|>{\raggedleft\arraybackslash}p{1.45cm}|>{\RaggedRight\arraybackslash}X|>{\raggedleft\arraybackslash}p{1.45cm}|>{\RaggedRight\arraybackslash}X|>{\raggedleft\arraybackslash}p{1.45cm}|}
\hline
<?php foreach ($totalRows as $row) { ?>
<?=LatexEscape($row[0]); ?> & \textbf{<?=LatexEscape($row[1]); ?>} & <?=LatexEscape($row[2]); ?> & \textbf{<?=LatexEscape($row[3]); ?>} & <?=LatexEscape($row[4]); ?> & \textbf{<?=LatexEscape($row[5]); ?>} \\\hline
<?php } ?>
\end{tabularx}
}

<?php if ($trainers !== []) { ?>
\vspace{0.55mm}
{\fontsize{6.0}{6.6}\selectfont\textbf{Formateurs intervenants}\par}
{\fontsize{5.4}{5.9}\selectfont
<?php attendance_register_render_trainer_grid($trainers); ?>
}
<?php } ?>

\vspace{0.55mm}
{\fontsize{6.0}{6.6}\selectfont\textbf{Signatures} -- Les signataires certifient l'exactitude du présent relevé.\par}
{\fontsize{5.2}{5.7}\selectfont
<?php attendance_register_render_signature_grid($signers); ?>
}
<?php
}

function BuildDocument(&$conf)
{
    $conf[".Engine"] = "latex";

    $registers = attendance_register_list($conf["Registers"] ?? []);
    if ($registers === [])
        $registers = [[]];
?>
---
geometry: margin=0.65cm, paperwidth=29.7cm, paperheight=21cm
output: pdf_document
lang: fr-FR
documentclass: article
indent: 0pt
pdf-engine: xelatex
---
```{=latex}
<?php foreach ($registers as $index => $register) { ?>
<?php
    if (!is_array($register))
        $register = [];
    $sheet = array_replace($conf, $register);
    unset($sheet["Registers"]);
    if ($index > 0)
        echo "\\clearpage\n";
    attendance_register_render_sheet($sheet);
?>
<?php } ?>
```
<?php
}
