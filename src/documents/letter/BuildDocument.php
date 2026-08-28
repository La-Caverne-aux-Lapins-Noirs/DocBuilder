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

function DocBuilderLetterFooter(array $conf)
{
    if (!isset($conf["Footer"]))
        return ("");
    if (is_string($conf["Footer"]))
        return ("\\fancyfoot[L]{%\n".
            "    \\begin{minipage}[t]{\\textwidth}\n".
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

    $from_x = DocBuilderLetterDimension($letter, "FromX", "2cm");
    $from_y = DocBuilderLetterDimension($letter, "FromY", "5cm");
    $from_width = DocBuilderLetterDimension($letter, "FromWidth", "7.5cm");
    $target_x = DocBuilderLetterDimension($letter, "TargetX", "11.5cm");
    $target_y = DocBuilderLetterDimension($letter, "TargetY", "5cm");
    $target_width = DocBuilderLetterDimension($letter, "TargetWidth", "7cm");
    $body_gap = DocBuilderLetterDimension($letter, "BodyGap", "3.5cm");
    $body_left = DocBuilderLetterDimension($letter, "BodyLeft", "1cm");
    $body_right = DocBuilderLetterDimension($letter, "BodyRight", "1cm");
?>
---
geometry: left=2cm, right=2cm, top=2.5cm, bottom=1.05cm, includehead, paperwidth=21cm, paperheight=29.7cm
output: pdf_document
lang: fr-FR
documentclass: article
indent: 0pt
pdf-engine: xelatex
---
\pagestyle{fancy}
\fancyhf{}
\setlength{\headheight}{2.5cm}
\setlength{\headsep}{0.45cm}
\setlength{\footskip}{0.18cm}
\setlength{\parindent}{0pt}

<?=DocBuilderLetterHeader($conf); ?>
<?=DocBuilderLetterFooter($conf); ?>

<?php if (isset($conf["From"]) && DocBuilderLetterFragment($conf["From"]) !== "") { ?>
\begin{textblock*}{<?=$from_width; ?>}(<?=$from_x; ?>,<?=$from_y; ?>)
\raggedright
<?=DocBuilderLetterFragment($conf["From"]); ?>
\end{textblock*}
<?php } ?>

<?php if ((isset($conf["Target"]) && DocBuilderLetterFragment($conf["Target"]) !== "") ||
          (isset($conf["Date"]) && DocBuilderLetterFragment($conf["Date"]) !== "")) { ?>
\begin{textblock*}{<?=$target_width; ?>}(<?=$target_x; ?>,<?=$target_y; ?>)
\raggedright
<?=isset($conf["Target"]) ? DocBuilderLetterFragment($conf["Target"]) : ""; ?>
<?php if (isset($conf["Date"]) && DocBuilderLetterFragment($conf["Date"]) !== "") { ?>
\par\vspace{0.35cm}
<?=DocBuilderLetterFragment($conf["Date"]); ?>
<?php } ?>
\end{textblock*}
<?php } ?>

\vspace*{<?=$body_gap; ?>}

\setlength{\leftskip}{<?=$body_left; ?>}
\setlength{\rightskip}{<?=$body_right; ?>}
\setlength{\parskip}{0.45em}

<?=isset($conf["Content"]) ? DocBuilderLetterFragment($conf["Content"]) : ""; ?>

<?php
}
