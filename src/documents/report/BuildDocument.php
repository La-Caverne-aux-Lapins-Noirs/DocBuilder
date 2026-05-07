<?php

function	BuildDocument(&$conf)
{
    $conf[".Engine"] = "latex";
    $student = $conf["People"]["Student"];
    $tutor = $conf["People"]["Tutor"];
    $director = $conf["People"]["Director"];
    $flames = 42;
    $total_flames = $flames + $conf["People"]["Student"]["PreviousFlames"];
    $obj_flames = 4 * ($conf["Cycle"]["Year"] - 1) + $conf["Cycle"]["Trimester"];
    $obj_flames *= 100;
    $general_comment = $conf["Cycle"]["Comment"];
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
    
<?php if (isset($conf["Header"]["Left"])) { ?>
    \fancyhead[L]{<?=$conf["Header"]["Left"]; ?>}
<?php } ?>
<?php if (isset($conf["Header"]["Center"])) { ?>
    \fancyhead[C]{<?=$conf["Header"]["Center"]; ?>}
<?php } ?>
<?php if (isset($conf["Header"]["Right"])) { ?>
    \fancyhead[R]{<?=$conf["Header"]["Right"]; ?>}
<?php } ?>
<?php if (isset($conf["Header"]) && is_string($conf["Header"])) { ?>
    \fancyhead[]{<?=$conf["Header"]; ?>}
<?php } ?>

\begin{textblock}{80}(110,60)
\noindent
<?=$tutor["Identity"]; ?>\\
<?=$tutor["Address"]; ?>
\end{textblock}

<?php if (isset($conf["Footer"]) && is_string($conf["Footer"])) { ?>
    \fancyfoot[L]{
        <?php $w = 21 - 2; ?>
        \begin{minipage}[t]{<?=$w; ?>cm}
            <?=$conf["Footer"]; ?>
        \end{minipage}
        \vspace*{\fill}
    }
<?php } else { ?>

    <?php if (isset($conf["Footer"]["Left"])) { ?>
	\fancyfoot[L]{<?=$conf["Footer"]["Left"]; ?>}
    <?php } ?>
    <?php if (isset($conf["Footer"]["Center"])) { ?>
	\fancyfoot[C]{<?=$conf["Footer"]["Center"]; ?>}
    <?php } ?>
    <?php if (isset($conf["Footer"]["Right"])) { ?>
	\fancyfoot[R]{<?=$conf["Footer"]["Center"]; ?>}
    <?php } ?>
    
<?php } ?>

# [@Size;7] Trimestre <?=$conf["Cycle"]["Code"]; ?> - <?=$student["Identity"]; ?>

\noindent
\textbf{Période}: <?=$conf["Cycle"]["Start"]; ?> - <?=$conf["Cycle"]["End"]; ?>
\
Année <?=$conf["Cycle"]["Year"]; ?>, trimestre <?=$conf["Cycle"]["Trimester"]; ?>
\
\textbf{Numéro étudiant}: <?=$student["Id"]; ?>
\
\textbf{INE}: <?=$student["INE"]; ?>
\

\noindent
\textbf{Objectif du trimestre}: 100
\
\textbf{Flammes acquises sur le trimestre}: <?=$flames; ?>
\
\textbf{Objectif à ce stade de la scolarité}: <?=$obj_flames; ?>
\
\textbf{Flammes acquises depuis le départ}: <?=$total_flames; ?>
\

<?php ///////////////////////////// ?>

[#Size;1]

\noindent\setlength{\parindent}{0pt}\renewcommand{\arraystretch}{1.3}\begin{tabularx}{\textwidth}{
|>{\centering\arraybackslash}p{2cm}
|>{\centering\arraybackslash}X
|>{\centering\arraybackslash}p{1.5cm}
|>{\centering\arraybackslash}p{1.5cm}
|>{\centering\arraybackslash}p{1.5cm}
|>{\centering\arraybackslash}X
|>{\centering\arraybackslash}p{1.0cm}
|>{\centering\arraybackslash}p{1.0cm}
|>{\centering\arraybackslash}p{1.0cm}
|}
\hline
\textbf{Code} & \textbf{Matière} & \textbf{Activité*} & \textbf{Examen*} & \textbf{Projets*} & \textbf{Commentaire} & \textbf{Grade} & \textbf{FL*} & \textbf{FA*} \\
\hline
<?php $max = $count = 0; ?>
<?php foreach ($conf["Cycle"]["Modules"] as $mod) {
    $code = LatexEscape($mod["Code"] ?? "");
    $name = LatexEscape($mod["Name"] ?? "");

    $act  = "P".($mod["Activities"]["Attendance"] ?? "0")
          ." A".($mod["Activities"]["NonAttendance"] ?? "0")
          ." N".($mod["Activities"]["Unregistered"] ?? "0");

    $exam = "P".($mod["Exam"]["Attendance"] ?? "0")
          ." A".($mod["Exam"]["NonAttendance"] ?? "0")
          ." N".($mod["Exam"]["Unregistered"] ?? "0");

    $work = "P".($mod["Work"]["Delivered"] ?? "0")
          ." A".($mod["Work"]["Undelivered"] ?? "0")
          ." N".($mod["Work"]["Unregistered"] ?? "0");

    $comment = LatexEscape($mod["Comment"] ?? "");
    $grade   = LatexEscape((string)($mod["Grade"] ?? ""));
    $flRes   = LatexEscape((string)($mod["Flames"]["Result"] ?? "0"));
    $fa      = LatexEscape((string)(($mod["Flames"]["Min"] ?? "")." - ".($mod["Flames"]["Max"] ?? "")));

    // échappe aussi les champs composés
    $act = LatexEscape($act);
    $exam = LatexEscape($exam);
    $work = LatexEscape($work);

    $max += $mod["Flames"]["Max"] ?? 0;
    $count += $mod["Flames"]["Result"] ?? 0;
?>
    <?= $code ?> & <?= $name ?> & <?= $act ?> & <?= $exam ?> & <?= $work ?> & <?= $comment ?> & <?= $grade ?> & <?= $flRes ?> & <?= $fa ?> \\
    \hline
<?php } ?>
\multicolumn{7}{|r|}{\textbf{Total }} & \textbf{<?=$count; ?>} & \textbf{<?=$max; ?>} \\
\hline
\end{tabularx}

\

[@Size;5]
\noindent
\*P: Présent ou rendu
\
\*A: Absent ou non rendu
\
\*N: Non inscrit
\
\*FL: Flammes
\
\*FA: Flammes accessibles
\

\vfill
\noindent
\par\vspace{0.5em}
\fbox{
  \parbox[c][5.5cm][c]{\textwidth}{
    \begin{minipage}[t][5.5cm][t]{0.66\textwidth}
Commentaire général
\

<?=isset($general_comment) ? LatexEscape($general_comment) : "" ?>
    \end{minipage}
    \hfill
    \begin{minipage}[t][4.5cm][t]{0.30\textwidth}
      \raggedleft
      Signature
\

      <?=isset($director["Identity"]) ? LatexEscape($director["Identity"])."\\\\" : "" ?>
      <?=LatexEscape($director["Role"] ?? "") ?>

      <?php if (isset($director["Signature"])) { ?>
        \vspace{2mm}
        \begin{center}
          [@Image;<?=$director["Signature"];?>;width=4cm;height=2cm]
        \end{center}
      <?php } ?>

      \vfill

      <?php if (isset($school_stamp)) { // Ca, on le fera a la main ?>
        \begin{center}
	  Tampon de l'établissement
          [@Image;<?=$school_stamp;?>;width=4cm;height=2cm]
        \end{center}
      <?php } else { ?>
          \begin{center}
	    \raggedleft
           Tampon de l'établissement
	    \vfill
          \end{center}
      <?php } ?>
    \end{minipage}
  }
}
     

<?php }
