<?php

function DocBuilderReportFragment($value)
{
    if (is_array($value))
    {
        $out = "";
        foreach ($value as $fragment)
            if (is_scalar($fragment))
                $out .= (string)$fragment."\n";
        return ($out);
    }
    return (is_scalar($value) ? (string)$value : "");
}

function DocBuilderReportDimension(array $layout, $key, $default)
{
    $value = isset($layout[$key]) ? trim((string)$layout[$key]) : "";
    if ($value === "" || preg_match('/^[0-9]+(?:\\.[0-9]+)?(?:cm|mm|pt|in)$/D', $value) !== 1)
        return ($default);
    return ($value);
}

function DocBuilderReportFooter(array $conf, $footer_x, $footer_y, $footer_width)
{
    if (!isset($conf["Footer"]))
        return ("");
    if (is_string($conf["Footer"]))
        return ("\\fancyfoot[L]{%\n".
            "    \\begin{textblock*}{".$footer_width."}(".$footer_x.",".$footer_y.")\n".
            "        \\setlength{\\leftskip}{0pt}\\setlength{\\rightskip}{0pt}%\n".
            "        \\hrule\\vspace{0.12cm}%\n".
            "        \\raggedright ".$conf["Footer"]."\n".
            "    \\end{textblock*}%\n".
            "}\n");
    if (!is_array($conf["Footer"]))
        return ("");

    $out = "";
    foreach (["Left" => "L", "Center" => "C", "Right" => "R"] as $key => $position)
        if (isset($conf["Footer"][$key]))
            $out .= "\\fancyfoot[".$position."]{".DocBuilderReportFragment($conf["Footer"][$key])."}\n";
    return ($out);
}

function BuildDocument(&$conf)
{
    $conf[".Engine"] = "latex";
    $extra_header = isset($conf[".LatexExtraHeader"]) && is_string($conf[".LatexExtraHeader"])
        ? rtrim($conf[".LatexExtraHeader"])."\n"
        : "";
    if (strpos($extra_header, "futura.ttf") === false)
        $extra_header .= "\\setmainfont[Path=/usr/share/docbuilder/res/,".
            "BoldFont=futura.ttf,ItalicFont=futura.ttf,BoldItalicFont=futura.ttf]{futura.ttf}\n";
    if (strpos($extra_header, "{longtable}") === false)
        $extra_header .= "\\usepackage{longtable}\n";
    if (strpos($extra_header, "{textpos}") === false)
        $extra_header .= "\\usepackage[absolute,overlay]{textpos}\n";
    $conf[".LatexExtraHeader"] = $extra_header;
    $student = $conf["People"]["Student"] ?? [];
    $recipient = $conf["People"]["Tutor"] ?? $student;
    $cycle = $conf["Cycle"] ?? [];
    $modules = $cycle["Modules"] ?? [];
    $current_flames = (int)($cycle["Flames"] ?? 0);
    $total_flames = (int)($cycle["TotalFlames"] ?? $current_flames);
    $objective = max(0, (int)($cycle["Objective"] ?? 100));
    $show_cumulative = false; // Le cumul de scolarité est conservé mais temporairement masqué.
    $cycle_manager = $conf["People"]["CycleManager"] ?? [];
    $director = $conf["People"]["Director"] ?? [];
    $layout = isset($conf["Letter"]) && is_array($conf["Letter"]) ? $conf["Letter"] : [];
    $page_bottom = DocBuilderReportDimension($layout, "BottomMargin", "0.8cm");
    $footer_height = DocBuilderReportDimension($layout, "FooterHeight", "2.2cm");
    $footer_rule_gap = DocBuilderReportDimension($layout, "FooterRuleGap", "0.12cm");
    $footer_x = DocBuilderReportDimension($layout, "FooterX", "2cm");
    $footer_y = DocBuilderReportDimension($layout, "FooterY", "27cm");
    $footer_width = DocBuilderReportDimension($layout, "FooterWidth", "17cm");

    $person_line = function(array $person) {
        $identity = LatexEscape((string)($person["Identity"] ?? $person["Name"] ?? ""));
        $address = LatexEscape((string)($person["Address"] ?? $person["Street"] ?? ""));
        $postal = LatexEscape((string)($person["PostalCity"] ?? ""));
        $country = LatexEscape((string)($person["Country"] ?? ""));
        return (implode("\\\\\n", array_filter([$identity, $address, $postal, $country], "strlen")));
    };
?>
---
geometry: left=2cm, right=2cm, top=1cm, bottom=<?=$page_bottom;?>, headheight=1cm, headsep=0.45cm, footskip=<?=$footer_height;?>, includeheadfoot, paperwidth=21cm, paperheight=29.7cm
output: pdf_document
lang: fr-FR
documentclass: article
indent: 0pt
table-caption-above: true
pdf-engine: xelatex
---
\pagestyle{fancy}
\fancyhf{}
\setlength{\headheight}{1cm}
\setlength{\headsep}{0.45cm}
\setlength{\footskip}{<?=$footer_height;?>}
\renewcommand{\footruleskip}{<?=$footer_rule_gap;?>}
\setlength{\tabcolsep}{2pt}
\setlength{\fboxsep}{0.2cm}
\setlength{\parindent}{0pt}
\renewcommand{\footrulewidth}{0pt}

<?php if (isset($conf["Header"]["Left"])) { ?>
\fancyhead[L]{<?=$conf["Header"]["Left"];?>}
<?php } ?>
<?php if (isset($conf["Header"]["Center"])) { ?>
\fancyhead[C]{<?=$conf["Header"]["Center"];?>}
<?php } ?>
<?php if (isset($conf["Header"]["Right"])) { ?>
\fancyhead[R]{\raisebox{-0.35cm}{<?=$conf["Header"]["Right"];?>}}
<?php } ?>

<?=DocBuilderReportFooter($conf, $footer_x, $footer_y, $footer_width);?>

\noindent
\Large \textbf{Bulletin de fin de trimestre}
\par
\vspace{2mm}
\large <?=LatexEscape((string)($cycle["Code"] ?? ""));?><?php if (trim((string)($cycle["Name"] ?? "")) != "") { ?> -- <?=LatexEscape((string)$cycle["Name"]);?><?php } ?>
\par
\normalsize

\vspace{4mm}
\noindent
\begin{minipage}[t]{0.40\textwidth}
\fbox{\begin{minipage}{0.92\linewidth}
\textbf{Élève :} <?=LatexEscape((string)($student["Identity"] ?? ""));?>\\
\textbf{Numéro étudiant :} <?=LatexEscape((string)($student["Id"] ?? ""));?>
\\
\textbf{INE :} <?=LatexEscape((string)($student["INE"] ?? ""));?>
<?php if (trim((string)($student["BirthDate"] ?? "")) != "") { ?>\\
\textbf{Né(e) le :} <?=LatexEscape((string)$student["BirthDate"]);?><?php if (trim((string)($student["BirthPlace"] ?? "")) != "") { ?>
\quad \textbf{à :} <?=LatexEscape((string)$student["BirthPlace"]);?><?php } ?>
<?php } ?>
\end{minipage}}
\end{minipage}
\hfill
\begin{minipage}[t]{0.41\textwidth}
<?=$person_line($recipient);?>
\end{minipage}

\vspace{22mm}
\begin{tabularx}{\textwidth}{@{}lXlX@{}}
\textbf{Période :} & <?=LatexEscape((string)($cycle["Start"] ?? ""));?> au <?=LatexEscape((string)($cycle["End"] ?? ""));?> &
\textbf{Édité le :} & <?=LatexEscape((string)($conf["Generation"]["Date"] ?? ""));?> \\
\textbf{Année :} & <?=LatexEscape((string)($cycle["Year"] ?? ""));?> &
\textbf{Trimestre :} & <?=LatexEscape((string)($cycle["Trimester"] ?? ""));?> \\
\end{tabularx}

\vspace{2mm}
\begin{tabularx}{\textwidth}{|X|>{\centering\arraybackslash}p{3.2cm}<?php if ($show_cumulative) { ?>|>{\centering\arraybackslash}p{3.2cm}<?php } ?>|}
\hline
\textbf{Indicateur} & \textbf{Trimestre}<?php if ($show_cumulative) { ?> & \textbf{Cumul scolarité}<?php } ?> \\
\hline
Flammes acquises & \textbf{<?=$current_flames;?>} / <?=$objective;?><?php if ($show_cumulative) { ?> & \textbf{<?=$total_flames;?>}<?php } ?> \\
\hline
\end{tabularx}

\vspace{3mm}
\begingroup
\scriptsize
\renewcommand{\arraystretch}{1.10}
\setlength{\LTleft}{0pt}
\setlength{\LTright}{0pt}
\begin{longtable}{
|>{\raggedright\arraybackslash}p{1.35cm}
|>{\raggedright\arraybackslash}p{6.7cm}
|>{\raggedright\arraybackslash}p{\dimexpr\textwidth-10.55cm-12\tabcolsep-7\arrayrulewidth\relax}
|>{\centering\arraybackslash}p{0.75cm}
|>{\centering\arraybackslash}p{0.75cm}
|>{\centering\arraybackslash}p{1.0cm}|}
\hline
\textbf{Code} & \textbf{Matière} & \textbf{Commentaire} & \textbf{Grade} & \textbf{FL*} & \textbf{FA*} \\
\hline
\endfirsthead
\hline
\textbf{Code} & \textbf{Matière} & \textbf{Commentaire} & \textbf{Grade} & \textbf{FL*} & \textbf{FA*} \\
\hline
\endhead
<?php $maximum = $obtained = 0; ?>
<?php foreach ($modules as $module) {
    $activity = sprintf("P%02d A%02d N%02d",
        (int)($module["Activities"]["Attendance"] ?? 0),
        (int)($module["Activities"]["NonAttendance"] ?? 0),
        (int)($module["Activities"]["Unregistered"] ?? 0));
    $exam = sprintf("P%02d A%02d N%02d",
        (int)($module["Exam"]["Attendance"] ?? 0),
        (int)($module["Exam"]["NonAttendance"] ?? 0),
        (int)($module["Exam"]["Unregistered"] ?? 0));
    $work = sprintf("P%02d A%02d N%02d",
        (int)($module["Work"]["Delivered"] ?? 0),
        (int)($module["Work"]["Undelivered"] ?? 0),
        (int)($module["Work"]["Unregistered"] ?? 0));
    $result = (int)($module["Flames"]["Result"] ?? 0);
    $max = (int)($module["Flames"]["Max"] ?? 0);
    $min = (int)($module["Flames"]["Min"] ?? 0);
    $obtained += $result;
    $maximum += $max;
?>
<?=LatexEscape((string)($module["Code"] ?? ""));?> &
\parbox[t]{\linewidth}{<?=LatexEscape((string)($module["Name"] ?? ""));?><?php if (trim((string)($module["Teachers"] ?? "")) != "") { ?>\par
{\tiny <?=LatexEscape((string)$module["Teachers"]);?>}<?php } ?>} &
<?=LatexEscape((string)($module["Comment"] ?? ""));?> &
<?=LatexEscape((string)($module["Grade"] ?? ""));?> & <?=$result;?> & <?=$min;?>--<?=$max;?> \\*
& & \mbox{\fontsize{4.5}{5}\selectfont
\textbf{Activités :} <?=LatexEscape($activity);?>\enspace
\textbf{Examens :} <?=LatexEscape($exam);?>\enspace
\textbf{Projets :} <?=LatexEscape($work);?>} & & & \\
\hline
<?php } ?>
\multicolumn{4}{|r|}{\textbf{Total}} & \textbf{<?=$obtained;?>} & \textbf{<?=$maximum;?>} \\
\hline
\end{longtable}
\endgroup

\begingroup\tiny
\noindent\textasteriskcentered{} P : présent ou rendu ; A : absent ou non rendu ; N : non inscrit ; FL : flammes obtenues ; FA : flammes accessibles.
\endgroup

<?php // Le cadre agrandi de 2 cm conserve la pagination précédente grâce à l'extension locale de la page. ?>
\enlargethispage{2cm}
\vspace{3mm}
\noindent\fbox{\begin{minipage}{\dimexpr\textwidth-2\fboxsep-2\fboxrule\relax}
\begin{minipage}[t][4.2cm][t]{0.68\linewidth}
\textbf{Commentaire du responsable de cycle}\\[1mm]
<?=LatexEscape((string)($cycle["Comment"] ?? ""));?>
\vfill
\textbf{Responsable de cycle :} <?=LatexEscape((string)($cycle_manager["Identity"] ?? ($cycle_manager["Name"] ?? "")));?>\hfill \textit{Signature}
\end{minipage}
\hfill
\begin{minipage}[t][4.2cm][t]{0.29\linewidth}
\raggedleft
\textbf{Visa de la direction}\\[1mm]
<?=LatexEscape((string)($director["Identity"] ?? ($director["Name"] ?? "")));?>\\
Directeur de l'établissement
\vfill
\textit{Signature et cachet}
\end{minipage}
\end{minipage}}

<?php
}
