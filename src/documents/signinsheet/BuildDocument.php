<?php

function SignInSheetText($value): string
{
    if (is_array($value))
        $value = $value[".this"] ?? "";
    return is_scalar($value) ? trim((string)$value) : "";
}

function SignInSheetFragment($value): string
{
    if (is_array($value))
    {
        $out = "";
        foreach ($value as $fragment)
            if (is_scalar($fragment))
                $out .= (string)$fragment."\n";
        return $out;
    }
    return is_scalar($value) ? (string)$value : "";
}

function SignInSheetList($value): array
{
    if (!is_array($value) || $value === [])
        return [];
    if (function_exists("array_is_list") && array_is_list($value))
        return $value;
    if (array_keys($value) === range(0, count($value) - 1))
        return $value;
    return [$value];
}

function SignInSheetDimension(array $layout, string $key, string $default): string
{
    $value = isset($layout[$key]) ? trim((string)$layout[$key]) : "";
    if ($value === "" || preg_match('/^[0-9]+(?:\\.[0-9]+)?(?:cm|mm|pt|in)$/D', $value) !== 1)
        return $default;
    return $value;
}

function SignInSheetPersonName($person): string
{
    if (!is_array($person))
        return SignInSheetText($person);
    $identity = SignInSheetText($person["Identity"] ?? "");
    if ($identity !== "")
        return $identity;
    $first = SignInSheetText($person["FirstName"] ?? "");
    $last = SignInSheetText($person["UseName"] ?? ($person["Name"] ?? ""));
    return trim($first." ".$last);
}

function SignInSheetTrue($value): bool
{
    if (is_bool($value))
        return $value;
    return in_array(strtolower(SignInSheetText($value)), ["1", "true", "yes", "oui"], true);
}

function SignInSheetLatexPath($value): string
{
    return str_replace(["{", "}", "\r", "\n"], "", SignInSheetText($value));
}

function SignInSheetPresenceCell($person, bool $applicable): string
{
    if (!$applicable || !is_array($person) || !SignInSheetTrue($person["Present"] ?? false))
        return "";

    $signature = SignInSheetLatexPath($person["Signature"] ?? "");
    if ($signature !== "" && is_file($signature))
        return "\\makebox[\\linewidth][c]{\\includegraphics[width=20mm,height=5.4mm,keepaspectratio]{\\detokenize{".$signature."}}}";

    return "\\makebox[\\linewidth][c]{{\\fontsize{5.8}{6.2}\\selectfont Présent}}";
}

function SignInSheetHeader(array $conf): string
{
    if (!isset($conf["Header"]))
        return "";
    if (is_string($conf["Header"]))
        return "\\fancyhead[]{".$conf["Header"]."}\n";
    if (!is_array($conf["Header"]))
        return "";

    $out = "";
    if (isset($conf["Header"]["Left"]))
        $out .= "\\fancyhead[L]{".SignInSheetFragment($conf["Header"]["Left"])."}\n";
    if (isset($conf["Header"]["Center"]))
        $out .= "\\fancyhead[C]{".SignInSheetFragment($conf["Header"]["Center"])."}\n";
    if (isset($conf["Header"]["Right"]))
        $out .= "\\fancyhead[R]{\\raisebox{-0.35cm}{".SignInSheetFragment($conf["Header"]["Right"])."}}\n";
    return $out;
}

function SignInSheetFooter(array $conf, string $footerHeight): string
{
    if (!isset($conf["Footer"]))
        return "";
    if (is_string($conf["Footer"]))
        return "\\fancyfoot[L]{%\n".
            "    \\begin{minipage}[t][".$footerHeight."][t]{\\textwidth}\n".
            "        \\vspace{0pt}%\n".
            "        \\setlength{\\leftskip}{0pt}\\setlength{\\rightskip}{0pt}%\n".
            "        \\centering ".$conf["Footer"]."\n".
            "    \\end{minipage}%\n".
            "}\n";
    if (!is_array($conf["Footer"]))
        return "";

    $out = "";
    foreach (["Left" => "L", "Center" => "C", "Right" => "R"] as $key => $position)
        if (isset($conf["Footer"][$key]))
            $out .= "\\fancyfoot[".$position."]{".SignInSheetFragment($conf["Footer"][$key])."}\n";
    return $out;
}

function SignInSheetPeopleTable(string $title, array $people, int $start, int $end,
                                string $morning, string $afternoon,
                                bool $morningApplicable, bool $afternoonApplicable): void
{
    $start = max(0, $start);
    $end = max($start, $end);
?>
\noindent{\fontsize{9.5}{10.5}\selectfont\bfseries <?=LatexEscape($title); ?>}\par
\vspace{0.7mm}
\begingroup\fontsize{6.8}{7.4}\selectfont
\setlength{\tabcolsep}{1mm}
\renewcommand{\arraystretch}{1.0}
\noindent\begin{tabular}{|>{\RaggedRight\arraybackslash}p{34mm}|>{\RaggedRight\arraybackslash}p{24.5mm}|>{\RaggedRight\arraybackslash}p{24.5mm}|>{\RaggedRight\arraybackslash}p{34mm}|>{\RaggedRight\arraybackslash}p{24.5mm}|>{\RaggedRight\arraybackslash}p{24.5mm}|}
\hline
\textbf{Nom et prénom} & \textbf{Matin}\newline <?=LatexEscape($morning); ?> & \textbf{Après-midi}\newline <?=LatexEscape($afternoon); ?> &
\textbf{Nom et prénom} & \textbf{Matin}\newline <?=LatexEscape($morning); ?> & \textbf{Après-midi}\newline <?=LatexEscape($afternoon); ?> \\\hline
<?php for ($i = $start; $i < $end; $i += 2) {
    $leftPerson = isset($people[$i]) && is_array($people[$i]) ? $people[$i] : [];
    $rightPerson = ($i + 1 < $end && isset($people[$i + 1]) && is_array($people[$i + 1])) ? $people[$i + 1] : [];
    $left = SignInSheetPersonName($leftPerson);
    $right = SignInSheetPersonName($rightPerson);
    $leftMorning = SignInSheetPresenceCell($leftPerson, $morningApplicable);
    $leftAfternoon = SignInSheetPresenceCell($leftPerson, $afternoonApplicable);
    $rightMorning = SignInSheetPresenceCell($rightPerson, $morningApplicable);
    $rightAfternoon = SignInSheetPresenceCell($rightPerson, $afternoonApplicable);
?>
<?=LatexEscape($left); ?> & <?=$leftMorning; ?> & <?=$leftAfternoon; ?> & <?=LatexEscape($right); ?> & <?=$rightMorning; ?> & <?=$rightAfternoon; ?> \rule{0pt}{7mm} \\\hline
<?php } ?>
\end{tabular}
\endgroup
<?php
}

function SignInSheetMetadata(array $sheet, int $page, int $pages): void
{
    $school = SignInSheetText($sheet["SchoolName"] ?? "Établissement non renseigné");
    $cycle = SignInSheetText($sheet["CycleName"] ?? "Élèves sans cycle associé");
    $activity = SignInSheetText($sheet["ActivityName"] ?? "");
    $dateTime = SignInSheetText($sheet["DateTime"] ?? "");
    $address = SignInSheetText($sheet["Address"] ?? "");
    if ($address === "")
        $address = "Adresse du centre non renseignée";
    $rooms = SignInSheetText($sheet["Rooms"] ?? "");
    $duration = SignInSheetText($sheet["Duration"] ?? "");
    $session = SignInSheetText($sheet["SessionId"] ?? "");
?>
\begingroup\fontsize{7.2}{8.0}\selectfont
\setlength{\tabcolsep}{1.6mm}
\renewcommand{\arraystretch}{1.12}
\noindent\begin{tabularx}{\textwidth}{|>{\RaggedRight\arraybackslash}X|>{\RaggedRight\arraybackslash}X|}
\hline
\textbf{Établissement}\par <?=LatexEscape($school); ?> & \textbf{Cycle de provenance}\par <?=LatexEscape($cycle); ?> \\\hline
\textbf{Formation / activité}\par <?=LatexEscape($activity); ?> & \textbf{Date et horaires planifiés}\par <?=LatexEscape($dateTime); ?> \\\hline
\textbf{Lieu de formation}\par <?=LatexEscape($address); ?><?php if ($rooms !== "") { ?>\newline <?=LatexEscape($rooms); ?><?php } ?> &
\textbf{Durée de formation planifiée (hors pause)}\par <?=LatexEscape($duration); ?><?php if ($session !== "") { ?>\newline {\fontsize{6.2}{6.8}\selectfont Référence : session \#<?=LatexEscape($session); ?> -- page <?=$page; ?>/<?=$pages; ?>}<?php } ?> \\\hline
\end{tabularx}
\endgroup
<?php
}

function SignInSheetRenderPage(array $sheet, array $students, array $trainers,
                               int $studentStart, int $studentEnd,
                               int $trainerStart, int $trainerEnd,
                               int $page, int $pages, bool $showStudents, bool $showTrainers): void
{
    $morning = SignInSheetText($sheet["Morning"] ?? "sans objet");
    $afternoon = SignInSheetText($sheet["Afternoon"] ?? "sans objet");
    $morningApplicable = array_key_exists("MorningApplicable", $sheet)
        ? SignInSheetTrue($sheet["MorningApplicable"]) : strtolower($morning) !== "sans objet";
    $afternoonApplicable = array_key_exists("AfternoonApplicable", $sheet)
        ? SignInSheetTrue($sheet["AfternoonApplicable"]) : strtolower($afternoon) !== "sans objet";

    SignInSheetMetadata($sheet, $page, $pages);
    if ($showStudents)
    {
        echo "\\vspace{2.2mm}\n";
        SignInSheetPeopleTable("Apprenants", $students, $studentStart, $studentEnd, $morning, $afternoon,
            $morningApplicable, $afternoonApplicable);
    }
    if ($showTrainers)
    {
        echo "\\vspace{2.2mm}\n";
        SignInSheetPeopleTable("Formateurs", $trainers, $trainerStart, $trainerEnd, $morning, $afternoon,
            $morningApplicable, $afternoonApplicable);
    }
?>
\vspace{1.8mm}
\begingroup\fontsize{6.4}{7}\selectfont Émargement manuscrit à recueillir pour les apprenants. Les cases non applicables restent vierges. Pour les formateurs déclarés présents, la signature de profil est incrustée lorsqu'elle existe ; à défaut, la mention « Présent » est reportée.\endgroup
<?php
}

function BuildDocument(&$conf)
{
    $conf[".Engine"] = "latex";
    $extraHeader = isset($conf[".LatexExtraHeader"]) && is_string($conf[".LatexExtraHeader"])
        ? rtrim($conf[".LatexExtraHeader"])."\n" : "";
    if (strpos($extraHeader, "futura.ttf") === false)
        $extraHeader .= "\\setmainfont[Path=/usr/share/docbuilder/res/,".
            "BoldFont=futura.ttf,ItalicFont=futura.ttf,BoldItalicFont=futura.ttf]{futura.ttf}\n";
    foreach (["tabularx", "array", "ragged2e"] as $package)
        if (strpos($extraHeader, "{".$package."}") === false)
            $extraHeader .= "\\usepackage{".$package."}\n";
    $conf[".LatexExtraHeader"] = $extraHeader;

    $layout = isset($conf["SignInSheetLayout"]) && is_array($conf["SignInSheetLayout"])
        ? $conf["SignInSheetLayout"] : [];
    $top = SignInSheetDimension($layout, "TopMargin", "1cm");
    $bottom = SignInSheetDimension($layout, "BottomMargin", "2cm");
    $headerHeight = SignInSheetDimension($layout, "HeaderHeight", "1cm");
    $headerGap = SignInSheetDimension($layout, "HeaderGap", "0.45cm");
    $footerHeight = SignInSheetDimension($layout, "FooterHeight", "2cm");
    $footerRuleGap = SignInSheetDimension($layout, "FooterRuleGap", "0.12cm");
    $studentsPerPage = max(2, (int)($layout["PeoplePerPage"] ?? 30));
    if ($studentsPerPage % 2 !== 0)
        --$studentsPerPage;
    $trainersOnStudentPage = max(0, (int)($layout["TrainerInlineLimit"] ?? 8));

    $sheets = SignInSheetList($conf["Sheets"] ?? []);
    if ($sheets === [])
        $sheets = [[]];
?>
---
geometry: left=1.4cm, right=1.4cm, top=<?=$top; ?>, bottom=<?=$bottom; ?>, headheight=<?=$headerHeight; ?>, headsep=<?=$headerGap; ?>, footskip=<?=$footerHeight; ?>, includeheadfoot, paperwidth=21cm, paperheight=29.7cm
output: pdf_document
lang: fr-FR
documentclass: article
indent: 0pt
pdf-engine: xelatex
---
\pagestyle{fancy}
\fancyhf{}
\setlength{\headheight}{<?=$headerHeight; ?>}
\setlength{\headsep}{<?=$headerGap; ?>}
\setlength{\footskip}{<?=$footerHeight; ?>}
\renewcommand{\footruleskip}{<?=$footerRuleGap; ?>}
\renewcommand{\headrulewidth}{0.4pt}
\renewcommand{\footrulewidth}{0pt}
\setlength{\parindent}{0pt}

<?=SignInSheetHeader($conf); ?>
<?=SignInSheetFooter($conf, $footerHeight); ?>

<?php
    $firstPage = true;
    foreach ($sheets as $sheet)
    {
        if (!is_array($sheet))
            $sheet = [];
        $students = SignInSheetList($sheet["Students"] ?? []);
        $trainers = SignInSheetList($sheet["Trainers"] ?? []);
        $capacity = max(0, (int)($sheet["Capacity"] ?? 0));
        $lines = max($capacity, count($students));
        $studentPages = max(1, (int)ceil($lines / $studentsPerPage));
        $trainerPages = count($trainers) > $trainersOnStudentPage
            ? (int)ceil(count($trainers) / $studentsPerPage) : 0;
        $totalPages = $studentPages + $trainerPages;

        for ($page = 0; $page < $studentPages; ++$page)
        {
            if (!$firstPage)
                echo "\\clearpage\n";
            $firstPage = false;
            $start = $page * $studentsPerPage;
            $end = min($lines, ($page + 1) * $studentsPerPage);
            $showTrainers = $page === $studentPages - 1 && count($trainers) <= $trainersOnStudentPage;
            SignInSheetRenderPage($sheet, $students, $trainers, $start, $end,
                0, max(2, count($trainers)), $page + 1, $totalPages, true, $showTrainers);
        }

        if ($trainerPages > 0)
            for ($offset = 0; $offset < count($trainers); $offset += $studentsPerPage)
            {
                if (!$firstPage)
                    echo "\\clearpage\n";
                $firstPage = false;
                $page = $studentPages + intdiv($offset, $studentsPerPage) + 1;
                SignInSheetRenderPage($sheet, $students, $trainers, 0, 0,
                    $offset, min(count($trainers), $offset + $studentsPerPage),
                    $page, $totalPages, false, true);
            }
    }
?>
<?php
}
