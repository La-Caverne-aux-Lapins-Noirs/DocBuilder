<?php

function subject_document_path(array $conf, array $path, $default = "")
{
    $value = $conf;
    foreach ($path as $part)
    {
        if (!is_array($value) || !array_key_exists($part, $value))
            return ($default);
        $value = $value[$part];
    }
    return ($value);
}

function subject_document_localized($value, $language, $default = "")
{
    if (is_scalar($value))
        return ((string)$value);
    if (!is_array($value))
        return ($default);
    foreach ([$language, strtoupper($language), strtolower($language), ".this"] as $key)
        if (isset($value[$key]) && is_scalar($value[$key]) && $value[$key] !== "")
            return ((string)$value[$key]);
    return ($default);
}

function subject_document_first(array $conf, array $paths, $default = "")
{
    foreach ($paths as $path)
    {
        $value = subject_document_path($conf, $path, "");
        if (is_scalar($value) && (string)$value !== "")
            return ((string)$value);
    }
    return ($default);
}

function subject_document_author(array $conf)
{
    $author = subject_document_path($conf, ["Activity", "Author"], []);
    if (is_array($author))
    {
        foreach ($author as $entry)
            if (is_array($entry) && ($entry["Name"] ?? "") != "")
                return ((string)$entry["Name"]);
        if (($author["Name"] ?? "") != "")
            return ((string)$author["Name"]);
    }
    $author = $conf["Author"] ?? "";
    if (is_scalar($author))
        return ((string)$author);
    return ("");
}

function subject_document_revision(array $conf, $language)
{
    $revision = subject_document_first($conf, [
        ["Activity", "LastRevision"],
        ["Activity", "Revision"],
        ["Revision"],
    ]);
    if ($revision == "")
        return ("");
    if (subject_document_path($conf, ["Activity", "LastRevision"], "") != "")
        return ($revision);
    return (($language == "FR" ? "Révision " : "Revision ").$revision);
}

function subject_document_append_header(array &$conf, $fragment)
{
    $header = isset($conf[".LatexExtraHeader"]) && is_string($conf[".LatexExtraHeader"])
        ? rtrim($conf[".LatexExtraHeader"])."\n"
        : "";
    if (strpos($header, $fragment) === false)
        $header .= $fragment."\n";
    $conf[".LatexExtraHeader"] = $header;
}

function subject_document_resource(array $conf, $path)
{
    if (!is_scalar($path) || (string)$path === "")
        return ("");
    $path = (string)$path;
    if ($path[0] === "/" && is_file($path))
        return ($path);
    if (is_file($path))
    {
        $resolved = realpath($path);
        return ($resolved !== false ? $resolved : $path);
    }
    foreach (($conf[".IncludePaths"] ?? []) as $directory)
    {
        $candidate = rtrim((string)$directory, "/")."/".$path;
        if (is_file($candidate))
        {
            $resolved = realpath($candidate);
            return ($resolved !== false ? $resolved : $candidate);
        }
    }
    return ($path);
}

function subject_document_tex_path($path)
{
    $path = str_replace(["\r", "\n"], ["", ""], (string)$path);
    return ("\\detokenize{".$path."}");
}

function subject_document_layout_id(array $exercise)
{
    $name = $exercise["Name"] ?? [];
    if (is_array($name) && isset($name[".this"]) && is_scalar($name[".this"]))
        return ((string)$name[".this"]);
    if (is_scalar($name))
        return ((string)$name);
    return ("");
}

function subject_document_layout(array $conf, array $exercise)
{
    $id = subject_document_layout_id($exercise);
    if ($id === "")
        return ([]);
    $layout = subject_document_path($conf, ["SubjectLayout", $id], []);
    return (is_array($layout) ? $layout : []);
}

function subject_document_render_block(array $conf, array $block, $fallbackText = "")
{
    $language = $conf["Language"];
    $text = subject_document_localized($block["Text"] ?? [], $language, $fallbackText);
    $image = subject_document_resource($conf, $block["Image"] ?? "");
    $align = strtolower((string)($block["ImageAlign"] ?? "right"));
    $style = strtolower((string)($block["TextStyle"] ?? ""));
    $width = (string)($block["ImageWidth"] ?? "0.43\\textwidth");

    if ($text === "" && $image === "")
        return;
    if ($image !== "")
    {
        $side = $align === "left" ? "l" : "r";
        ?>
\begin{wrapfigure}{<?=$side?>}{<?=$width?>}
\centering
\includegraphics[width=\linewidth]{<?=subject_document_tex_path($image)?>}
\end{wrapfigure}
        <?php
    }
    if ($style === "italic") {
        ?>
\begin{itshape}
        <?php
    }
    if ($text !== "") {
        ?>
[@Size; 4] <?=$text?>

        <?php
    }
    if ($style === "italic") {
        ?>
\end{itshape}
        <?php
    }
    ?>
\par
\FloatBarrier
    <?php
}

function subject_document_exercise_list(array $conf)
{
    $language = $conf["Language"];
    $index = 1;
    $i = 0;
    while (isset($conf["Exercises"][$i]))
    {
        $exercise = $conf["Exercises"][$i];
        if (isset($exercise["NoDoc"]))
        {
            ++$i;
            continue;
        }
        $description = subject_document_localized(
            subject_document_path($exercise, ["Document", "Description"], []),
            $language,
            ""
        );
        if ($description === "")
        {
            ++$i;
            continue;
        }
        $layout = subject_document_layout($conf, $exercise);
        $name = subject_document_localized(
            $layout["Name"] ?? ($exercise["Name"] ?? []),
            $language,
            ""
        );
        if ($name !== "")
        {
            $number = str_pad((string)$index, 2, "0", STR_PAD_LEFT);
            ?>
<?=$number?> – <?=$name?>
            <?php
            ++$index;
        }
        ++$i;
    }
}

function BuildDocument(&$conf)
{
    $conf[".Engine"] = "latex";
    $language = strtoupper((string)($conf["Language"] ?? "FR"));
    $conf["Language"] = $language;
    subject_document_append_header($conf, "\\usepackage{wrapfig}");
    subject_document_append_header($conf, "\\usepackage{placeins}");

    $schoolName = subject_document_first($conf, [
        ["School", "Name"],
        ["SchoolName"],
    ]);
    if ($schoolName == "")
        $schoolName = subject_document_localized($conf["School"] ?? [], $language, "");

    $matterName = subject_document_localized($conf["Matter"] ?? [], $language, "");
    $activityName = subject_document_localized($conf["Activity"] ?? [], $language, "");
    $activityDescription = subject_document_localized(
        subject_document_path($conf, ["Activity", "Description"], []),
        $language,
        ""
    );
    $frontMessage = subject_document_localized(
        subject_document_path($conf, ["FrontPage", "Message"], []),
        $language,
        $activityName
    );
    $frontDescription = subject_document_localized(
        subject_document_path($conf, ["FrontPage", "Description"], []),
        $language,
        $activityDescription
    );

    $schoolLogo = subject_document_resource($conf, subject_document_first($conf, [
        ["FrontPage", "SchoolLogo"],
        ["School", "DocumentLogo"],
        ["School", "Logo"],
        ["Matter", "Logo"],
    ]));
    $subjectLogo = subject_document_resource($conf, subject_document_first($conf, [
        ["FrontPage", "Logo"],
        ["Laboratory", "Logo"],
        ["Activity", "Logo"],
        ["Matter", "Logo"],
    ]));
    $coverImage = subject_document_resource(
        $conf,
        subject_document_path($conf, ["FrontPage", "CoverImage"], "")
    );
    if ($coverImage === "")
        $coverImage = $subjectLogo;
    $headerLogo = subject_document_resource($conf, subject_document_first($conf, [
        ["Laboratory", "SmallLogo"],
        ["Laboratory", "Logo"],
        ["Matter", "SmallLogo"],
        ["School", "DocumentLogo"],
        ["School", "Logo"],
    ]));
    $footerLogo = subject_document_resource($conf, subject_document_first($conf, [
        ["Activity", "SmallLogo"],
    ]));
    $revision = subject_document_revision($conf, $language);
    $author = subject_document_author($conf);
    ?>

    ---
    geometry: margin=0cm, paperwidth=21cm, paperheight=29.7cm
    output: pdf_document
    lang: <?=$language == "FR" ? "fr-FR" : "en-US"?>
    documentclass: article
    indent: 2pt
    table-caption-above: true
    pdf-engine: xelatex
    ---
    \pagestyle{fancy}
    \fancyhf{}

    \setlength{\headheight}{3cm}
    \setlength{\headsep}{1cm}
    \setlength{\textheight}{23cm}
    \setlength{\tabcolsep}{0pt}
    \setlength{\fboxsep}{0.2cm}

    <?php // Style pour la page de présentation ?>
    \fancypagestyle{presentationPage}{
        \fancyhf{}
    <?php if ($matterName != "") { ?>
        \fancyhead[R]{<?=$matterName?>}
    <?php } ?>
    <?php if ($revision != "" || $author != "") { ?>
        \fancyfoot[L]{
    <?php if ($revision != "") { ?>
            <?=$revision?>
    <?php } ?>
    <?php if ($revision != "" && $author != "") { ?>
            \\
    <?php } ?>
    <?php if ($author != "") { ?>
            <?=$language == "FR" ? "Auteur" : "Author"?> : <?=$author?>
    <?php } ?>
        }
    <?php } ?>
    <?php if ($schoolName != "") { ?>
        \fancyfoot[R]{\raisebox{-.5\height}{<?=$schoolName?>}}
    <?php } ?>
    }

    <?php // Style pour toutes les pages du sujet sauf la première de présentation ?>
    \fancypagestyle{contentPage}{
        \fancyhf{}
    <?php if ($matterName != "") { ?>
        \fancyhead[R]{<?=$matterName?>}
    <?php } ?>
    <?php if ($headerLogo != "") { ?>
        \fancyhead[L]{\includegraphics[width=2cm]{<?=subject_document_tex_path($headerLogo)?>}}
    <?php } ?>
    <?php if ($schoolName != "") { ?>
        \fancyfoot[R]{\raisebox{-.5\height}{<?=$schoolName?>}}
    <?php } ?>
        \fancyfoot[C]{\raisebox{-.5\height}{\thepage{}}}
    <?php if ($footerLogo != "") { ?>
        \fancyfoot[L]{\raisebox{-.5\height}{\includegraphics[width=2cm]{<?=subject_document_tex_path($footerLogo)?>}}}
    <?php } ?>
    }

    \savegeometry{default}
    \thispagestyle{presentationPage}
    \newgeometry{lmargin=2cm, rmargin=3cm, tmargin=2cm, bmargin=2cm}
    <?php if ($schoolLogo != "") { ?>
        \includegraphics[width=6cm, height=6cm, keepaspectratio]{<?=subject_document_tex_path($schoolLogo)?>}
    <?php } else { ?>
        \framebox{\parbox[c][6cm][c]{6cm}{<?=$schoolName?>}}
    <?php } ?>

    <?php if ($coverImage != "") { ?>
        \includegraphics[width=\textwidth, height=10cm, keepaspectratio]{<?=subject_document_tex_path($coverImage)?>}
    <?php } else { ?>
        \framebox{\parbox[c][10cm][c]{\textwidth}{# <?=$activityName?>}}
    <?php } ?>

    \mbox{\parbox[c][9cm][c]{\textwidth}{

    <?php if ($frontMessage != "") { ?>
        [@Size;5] [@Center;<?=$frontMessage?>]
    <?php } ?>

    <?php if ($frontDescription != "") { ?>
        [@Size;4] [@Center;<?=$frontDescription?>]
    <?php } ?>

    <?php if (!empty($conf["FrontPage"]["ExerciseList"])) { ?>
        \vfill
        [@Size;4]
        <?php subject_document_exercise_list($conf); ?>
    <?php } ?>

        \vfill
        \begin{itshape}
    <?php if ($language == "FR") { ?>
        [@Center; Ce document est strictement personnel et ne doit en aucun cas être diffusé.]
    <?php } else { ?>
        [@Center; This document is strictly personal and must not be shared under any circumstances.]
    <?php } ?>
        \end{itshape}
    }}

    [@NewPage]
    \loadgeometry{default}
    \pagestyle{contentPage}

    <?php
    $i = 0;
    $directory = "";
    $directoryStack = [];
    $sectionIndex = 1;
    $numberSections = !empty($conf["FrontPage"]["NumberSections"]);
    while (isset($conf["Exercises"][$i])) {
        $exercise = $conf["Exercises"][$i];

        // Move est une information structurelle commune à Evaluator et DocBuilder.
        if (isset($exercise["Module"]) && $exercise["Module"] == "Move" &&
            isset($exercise["Target"])) {
            $target = $exercise["Target"];
            if ($target == "-") {
                if (count($directoryStack) > 0)
                    $directory = array_pop($directoryStack);
                else
                    $directory = "";
            } else {
                $directoryStack[] = $directory;
                if (substr($target, 0, 1) == "/")
                    $path = $target;
                else if ($directory == "")
                    $path = $target;
                else
                    $path = $directory."/".$target;

                $absolute = substr($path, 0, 1) == "/";
                $parts = [];
                foreach (explode("/", $path) as $part) {
                    if ($part == "" || $part == ".")
                        continue;
                    if ($part == "..") {
                        if (count($parts) > 0 && end($parts) != "..")
                            array_pop($parts);
                        else if (!$absolute)
                            $parts[] = $part;
                    } else {
                        $parts[] = $part;
                    }
                }
                $directory = ($absolute ? "/" : "").implode("/", $parts);
            }
        }

        if (isset($exercise["NoDoc"])) {
            ++$i;
            continue;
        }

        $description = subject_document_localized(
            subject_document_path($exercise, ["Document", "Description"], []),
            $language,
            ""
        );
        if ($description != "") {
            $layout = subject_document_layout($conf, $exercise);
            $name = subject_document_localized(
                $layout["Name"] ?? ($exercise["Name"] ?? []),
                $language,
                ""
            );
            $pageModel = strtolower((string)($layout["PageModel"] ?? "standard"));
            $mandatoryFiles = $layout["MandatoryFiles"] ?? subject_document_path(
                $exercise,
                ["Document", "MandatoryFiles"],
                ""
            );
            if (is_array($mandatoryFiles))
                $mandatoryFiles = implode(", ", $mandatoryFiles);

            if ($name != "") {
                if ($numberSections) {
                    $number = str_pad((string)$sectionIndex, 2, "0", STR_PAD_LEFT);
                    ?> ## [@Size;9] <?=$number?> - <?=$name?>

                    <?php
                } else {
                    ?> ## [@Size;9] <?=$name?>

                    <?php
                }
            }
            if ($directory != "") {
                if ($language == "FR") {
                    ?> [@Size; 5] Dossier de ramassage : <?=$directory?>

                    <?php
                } else {
                    ?> [@Size; 5] Pickup directory : <?=$directory?>

                    <?php
                }
            }
            if ($mandatoryFiles != "") {
                if ($language == "FR") {
                    ?> [@Size; 5] Fichiers obligatoires : <?=$mandatoryFiles?>
                    \\
                    \\

                    <?php
                } else {
                    ?> [@Size; 5] Mandatory files : <?=$mandatoryFiles?>
                    \\
                    \\

                    <?php
                }
            }

            if ($pageModel === "horizontalsplit") {
                $topBlock = is_array($layout["TopBlock"] ?? NULL)
                    ? $layout["TopBlock"]
                    : [];
                $bottomBlock = is_array($layout["BottomBlock"] ?? NULL)
                    ? $layout["BottomBlock"]
                    : [];
                subject_document_render_block($conf, $topBlock, $description);
                if (count($bottomBlock) > 0) {
                    ?>
\vspace{0.25cm}
\hrule
\vspace{0.35cm}
                    <?php
                    subject_document_render_block($conf, $bottomBlock, "");
                }
            } else {
                ?> [@Size; 4] <?=$description?>

                <?php
            }
            ?>
            [@NewPage]

            <?php
            ++$sectionIndex;
        }
        ++$i;
    } ?>
<?php }
