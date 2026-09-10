<?php

function DocBuilderLetterFragment($value)
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

function DocBuilderLetterDimension(array $letter, $key, $default)
{
    $value = isset($letter[$key]) ? trim((string)$letter[$key]) : "";
    if ($value === "" || preg_match('/^[0-9]+(?:\\.[0-9]+)?(?:cm|mm|pt|in)$/D', $value) !== 1)
        return ($default);
    return ($value);
}

function DocBuilderLetterHeader(array $conf)
{
    if (!isset($conf["Header"]))
        return ("");
    if (is_string($conf["Header"]))
        return ("\\fancyhead[]{".$conf["Header"]."}\n");
    if (!is_array($conf["Header"]))
        return ("");

    $out = "";
    if (isset($conf["Header"]["Left"]))
        $out .= "\\fancyhead[L]{".DocBuilderLetterFragment($conf["Header"]["Left"])."}\n";
    if (isset($conf["Header"]["Center"]))
        $out .= "\\fancyhead[C]{".DocBuilderLetterFragment($conf["Header"]["Center"])."}\n";
    if (isset($conf["Header"]["Right"]))
        $out .= "\\fancyhead[R]{\\raisebox{-0.35cm}{".DocBuilderLetterFragment($conf["Header"]["Right"])."}}\n";
    return ($out);
}

function DocBuilderLetterFooter(array $conf, $footer_height)
{
    if (!isset($conf["Footer"]))
        return ("");
    if (is_string($conf["Footer"]))
        return ("\\fancyfoot[L]{%\n".
            "    \\begin{minipage}[t][".$footer_height."][t]{\\textwidth}\n".
            "        \\vspace{0pt}%\n".
            "        \\setlength{\\leftskip}{0pt}\\setlength{\\rightskip}{0pt}%\n".
            "        \\raggedright ".$conf["Footer"]."\n".
            "    \\end{minipage}%\n".
            "}\n");
    if (!is_array($conf["Footer"]))
        return ("");

    $out = "";
    foreach (["Left" => "L", "Center" => "C", "Right" => "R"] as $key => $position)
        if (isset($conf["Footer"][$key]))
            $out .= "\\fancyfoot[$position]{".DocBuilderLetterFragment($conf["Footer"][$key])."}\n";
    return ($out);
}

function BuildDocument(&$conf)
{
    $conf[".Engine"] = "latex";
    $letter = isset($conf["Letter"]) && is_array($conf["Letter"]) ? $conf["Letter"] : [];
    
    $page_top = DocBuilderLetterDimension($letter, "TopMargin", "1cm");
    $page_bottom = DocBuilderLetterDimension($letter, "BottomMargin", "0.8cm");
    $header_height = DocBuilderLetterDimension($letter, "HeaderHeight", "2.5cm");
    $header_gap = DocBuilderLetterDimension($letter, "HeaderGap", "0.45cm");
    $footer_height = DocBuilderLetterDimension($letter, "FooterHeight", "2.2cm");
    $footer_rule_gap = DocBuilderLetterDimension($letter, "FooterRuleGap", "0.12cm");
    $letter_meta_gap = DocBuilderLetterDimension($letter, "LetterMetaGap", "0.45cm");
    $from_x = DocBuilderLetterDimension($letter, "FromX", "2cm");
    $from_y = DocBuilderLetterDimension($letter, "FromY", "5cm");
    $from_width = DocBuilderLetterDimension($letter, "FromWidth", "7.5cm");
    $target_x = DocBuilderLetterDimension($letter, "TargetX", "11.5cm");
    $target_y = DocBuilderLetterDimension($letter, "TargetY", "5cm");
    $target_width = DocBuilderLetterDimension($letter, "TargetWidth", "7cm");
    $has_subject = isset($conf["Subject"]) && DocBuilderLetterFragment($conf["Subject"]) !== ""; 
    $date_y = isset($letter["DateY"]) ? DocBuilderLetterDimension($letter, "DateY", "") : "";
    $date_x = DocBuilderLetterDimension($letter, "DateX", $target_x);
    $date_width = DocBuilderLetterDimension($letter, "DateWidth", $target_width);
    $body_gap = DocBuilderLetterDimension($letter, "BodyGap", "3.5cm");
    $body_left = DocBuilderLetterDimension($letter, "BodyLeft", "1cm");
    $body_right = DocBuilderLetterDimension($letter, "BodyRight", "1cm");
?>
---
geometry: left=2cm, right=2cm, top=<?=$page_top; ?>, bottom=<?=$page_bottom; ?>, headheight=<?=$header_height; ?>, headsep=<?=$header_gap; ?>, footskip=<?=$footer_height; ?>, includeheadfoot, paperwidth=21cm, paperheight=29.7cm
output: pdf_document
lang: fr-FR
documentclass: article
indent: 0pt
pdf-engine: xelatex
---
\pagestyle{fancy}
\fancyhf{}
\setlength{\headheight}{<?=$header_height; ?>}
\setlength{\headsep}{<?=$header_gap; ?>}
\setlength{\footskip}{<?=$footer_height; ?>}
\renewcommand{\footruleskip}{<?=$footer_rule_gap; ?>}
\setlength{\parindent}{0pt}

<?=DocBuilderLetterHeader($conf); ?>
<?=DocBuilderLetterFooter($conf, $footer_height); ?>

<?php if (isset($conf["From"]) && DocBuilderLetterFragment($conf["From"]) !== "") { ?>
\begin{textblock*}{<?=$from_width; ?>}(<?=$from_x; ?>,<?=$from_y; ?>)
\raggedright
<?=DocBuilderLetterFragment($conf["From"]); ?>
\end{textblock*}
<?php } ?>

<?php if (isset($conf["Target"]) && DocBuilderLetterFragment($conf["Target"]) !== "") { ?>
\begin{textblock*}{<?=$target_width; ?>}(<?=$target_x; ?>,<?=$target_y; ?>)
\raggedright
<?=DocBuilderLetterFragment($conf["Target"]); ?> 
<?php if ($date_y === "" && !$has_subject && isset($conf["Date"]) && DocBuilderLetterFragment($conf["Date"]) !== "") { ?>
\par\vspace{0.35cm}
<?=DocBuilderLetterFragment($conf["Date"]); ?>
<?php } ?>
\end{textblock*}
<?php } ?>

<?php if (!$has_subject && $date_y !== "" && isset($conf["Date"]) && DocBuilderLetterFragment($conf["Date"]) !== "") { ?>
\begin{textblock*}{<?=$date_width; ?>}(<?=$date_x; ?>,<?=$date_y; ?>)
\raggedright
<?=DocBuilderLetterFragment($conf["Date"]); ?>
\end{textblock*}
<?php } else if (!$has_subject && (!isset($conf["Target"]) || DocBuilderLetterFragment($conf["Target"]) === "") &&
                  isset($conf["Date"]) && DocBuilderLetterFragment($conf["Date"]) !== "") { ?>
\begin{textblock*}{<?=$target_width; ?>}(<?=$target_x; ?>,<?=$target_y; ?>)
\raggedright
<?=DocBuilderLetterFragment($conf["Date"]); ?>
\end{textblock*}
<?php } ?>

\vspace*{<?=$body_gap; ?>}

\setlength{\leftskip}{<?=$body_left; ?>}
\setlength{\rightskip}{<?=$body_right; ?>}
\setlength{\parskip}{0.45em}

<?php if ($has_subject) { ?>
\noindent\textbf{<?=DocBuilderLetterFragment($conf["Subject"]); ?>}\par
<?php if (isset($conf["Date"]) && DocBuilderLetterFragment($conf["Date"]) !== "") { ?>
\vspace{<?=$letter_meta_gap; ?>}
\noindent\hfill <?=DocBuilderLetterFragment($conf["Date"]); ?>\par
<?php } ?>
\vspace{<?=$letter_meta_gap; ?>}
<?php } ?>

<?=isset($conf["Content"]) ? DocBuilderLetterFragment($conf["Content"]) : ""; ?>

<?php
}
