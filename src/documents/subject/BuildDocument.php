<?php

function	BuildDocument(&$conf) {

    $conf[".Engine"] = "latex";
    ?>

    ---
    geometry: margin=0cm, paperwidth=21cm, paperheight=29.7cm
    output: pdf_document
    lang: fr-FR
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
        <?php if (isset($conf["Matter"][$conf["Language"]])) { ?>
            \fancyhead[R]{<?=$conf["Matter"][$conf["Language"]]; ?>}
        <?php } ?>
    <?php if (isset($conf["Activity"]["LastRevision"]) && isset($conf["Activity"]["Author"][0]["Name"])) { ?>
        \fancyfoot[L]{Last Update : <?=$conf["Activity"]["LastRevision"];?> \\ Author : <?=$conf["Activity"]["Author"][0]["Name"];?> }
    <?php } ?>
    <?php if (isset($conf["SchoolName"])) { ?>
        \fancyfoot[R]{\raisebox{-.5\height}{<?= $conf["SchoolName"] ?>}}
    <?php } else { ?>
        \fancyfoot[R]{\raisebox{-.5\height}{Efrits}}
    <?php } ?>
    }

    <?php // Style pour toutes les pages du sujet sauf la première de présentation ?>
    <?php // Les footers ne sont pas en haut, le mieux est avec la raisebox{-.5\height} qui centre au milieu ?>
    <?php // 0 et \height font descendre le texte dans tous les cas, je n'ai pas trouvé de solutions ?>
    \fancypagestyle{contentPage}{
        \fancyhf{}
    <?php if (isset($conf["Matter"][$conf["Language"]])) { ?>
        \fancyhead[R]{<?=$conf["Matter"][$conf["Language"]]; ?>}
    <?php } ?>
    <?php if (isset($conf["Matter"]["SmallLogo"])) { ?>
        \fancyhead[L]{\includegraphics[width=2cm]{<?=$conf["Matter"]["SmallLogo"]; ?>}}
    <?php } ?>
    <?php if (isset($conf["SchoolName"])) { ?>
        \fancyfoot[R]{\raisebox{-.5\height}{<?= $conf["SchoolName"] ?>}}
    <?php } else { ?>
        \fancyfoot[R]{\raisebox{-.5\height}{Efrits}}
    <?php } ?>
        \fancyfoot[C]{\raisebox{-.5\height}{\thepage{}}}
    <?php if (isset($conf["Activity"]["SmallLogo"])) { ?>
        \fancyfoot[L]{\raisebox{-.5\height}{\includegraphics[width=2cm]{<?=$conf["Activity"]["SmallLogo"]; ?>}}}
    <?php } ?>
    }

    <?php // savegeometry{default} permet de sauvegarder les marges par défaut pour les remettre après la page de présentation ?>
    \savegeometry{default}
    <?php // thispagestyle applique le style défini au dessus à la page actuelle?>
    \thispagestyle{presentationPage}
    <?php // Applique des marges spéciales pour bien centré les images au milieu de la page ?>
    \newgeometry{lmargin=2cm, rmargin=3cm, tmargin=2cm, bmargin=2cm}
    <?php if (isset($conf["Matter"]["Logo"])) { ?>
        \includegraphics[width=6cm, height=6cm]{<?=$conf["Matter"]["Logo"]; ?>}
    <?php } else { // framebox et parbox permettent de faire des boîtes pour ne pas détruire la mise en page si pas d'image?>
        \framebox{\parbox[c][6cm][c]{6cm}{<?=$conf["Matter"][$conf["Language"]]; ?>}
    <?php } ?>

    <?php if (isset($conf["Activity"]["Logo"])) { ?>
        \includegraphics[width=\textwidth, height=10cm]{<?=$conf["Activity"]["Logo"]; ?>}
    <?php } else { ?>
        \framebox{\parbox[c][10cm][c]{\textwidth}{# <?=$conf["Activity"][$conf["Language"]]; ?>}
    <?php } ?>

    <?php // mbox est l'équivalent de framebox mais sans contour ?>
    \mbox{\parbox[c][9cm][c]{\textwidth}{

        [@Size;5] [@Center;<?=$conf["FrontPage"]["Message"][$conf["Language"]]; ?>]




        [@Size;4] [@Center;<?=$conf["FrontPage"]["Description"][$conf["Language"]]; ?>]


        \vfill
        \begin{itshape}
        <?php if ($conf["Language"] == "FR") { ?>
                [@Center; Ce document est strictement personnel et ne doit en aucun cas être diffusé.]
        <?php } else { ?>
                [@Center; This document is strictly personal and must not be shared under any circumstances.]
        <?php } ?>
        \end{itshape}
    }}

    [@NewPage]
    <?php // loadgeometry applique la geometry par défaut sauvegardé précédemment ?>
    \loadgeometry{default}
    <?php // Applique le style pour toutes les prochaines pages contrairement à thispagestyle qui l'applique que pour l'actuel ?>
    \pagestyle{contentPage}

    <?php $i = 0;
    $directory = "";
    $name = "";
    $description = "";
    $mandatoryFiles = "";
    while (isset($conf["Exercises"][$i])) {
        if (isset($conf["Exercises"][$i]["NoDoc"]))
            continue;
        if (isset($conf["Exercises"][$i]["Module"]) && $conf["Exercises"][$i]["Module"] == "Move") {
            if ($conf["Exercises"][$i]["Target"] != "-") {
                $directory = $conf["Exercises"][$i]["Target"];
            } else {
                if ($name != "")
                {
                    ?> ## [@Size;9] <?=$name?>


                    <?php $name = "";
                }
                if ($directory != "") {
                    if ($conf["Language"] == "FR") {
                        ?> [@Size; 5] Dossier de ramassage : <?=$directory?>


                    <?php } else {
                        ?> [@Size; 5] Pickup directory : <?=$directory?>


                    <?php }
                    $directory = "";
                }
                if ($mandatoryFiles != "") {
                    if ($conf["Language"] == "FR") {
                        ?> [@Size; 5] Fichiers obligatoires : <?=$mandatoryFiles?>
                        \
                        \

                    <?php } else {
                        ?> [@Size; 5] Mandatory files : <?=$mandatoryFiles?>
                        \
                        \

                    <?php }
                    $mandatoryFiles = "";
                }
                if ($description != "") {
                    ?> [@Size; 4] <?=$description?>

                    <?php
                    $description = "";
                }
               ?>[@NewPage]

                <?php
            }
        }
        if (isset($conf["Exercises"][$i]["Type"]) && $conf["Exercises"][$i]["Type"] == "Function") {
            if (isset($conf["Exercises"][$i]["Name"][".this"])) {
                $name = $conf["Exercises"][$i]["Name"][".this"];
            }
            if (isset($conf["Exercises"][$i]["Document"]["Description"][$conf["Language"]])) {
                $description = $conf["Exercises"][$i]["Document"]["Description"][$conf["Language"]];
            }
            if (isset($conf["Exercises"][$i]["Document"]["MandatoryFiles"])) {
                $mandatoryFiles = $conf["Exercises"][$i]["Document"]["MandatoryFiles"];
                if (is_array($mandatoryFiles)) {
                    $mandatoryFiles = implode(", ", $mandatoryFiles);
                }
            }
        }
        $i += 1;
    } ?>
<?php }