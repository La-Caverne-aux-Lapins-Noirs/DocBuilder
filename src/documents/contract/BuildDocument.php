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

<?php if (isset($conf["Footer"]) && is_string($conf["Footer"])) { ?>
    \fancyfoot[L]{%
        <?php $w = 21 - 2 - count($conf["Signatories"]) * 3; ?>
        % fancyhdr aligne les pieds gauche et droit sur une ligne de base commune.
        % Le bloc de paraphes étant nettement plus haut que le texte légal, celui-ci
        % se retrouvait visuellement collé au bas de la page. On le remonte pour
        % aligner son début avec le haut du bloc de paraphes, juste sous le filet.
        \raisebox{0.75cm}[0pt][0pt]{%
            \begin{minipage}[t]{<?=$w; ?>cm}
                \vspace{0pt}% force l'alignement sur le haut réel de la minipage
                <?=$conf["Footer"]; ?>
            \end{minipage}%
        }%
    }
<?php } else { ?>

    <?php if (isset($conf["Footer"]["Left"])) { ?>
	\fancyfoot[L]{<?=$conf["Footer"]["Left"]; ?>}
    <?php } ?>
    <?php if (isset($conf["Footer"]["Center"])) { ?>
	\fancyfoot[C]{<?=$conf["Footer"]["Center"]; ?>}
    <?php } ?>

<?php } ?>

\fancyfoot[R]{
    \begin{tabular}{<?php foreach ($conf["Signatories"] as $k => $v) echo "@{}c "; ?>}
        \textit{\centering \scriptsize Paraphes} \\[0.25em]
            <?php $i = 0; $len = count($conf["Signatories"]); ?>
            <?php foreach ($conf["Signatories"] as $k => $v) { ?>
	        \fbox{\parbox[c][1.0cm][t]{2cm}{
                    \tiny <?=$v["Role"]; ?>
		    <?php if (isset($v["Initials"]) && is_string($v["Initials"]) && trim($v["Initials"]) != "") { ?>
			\begin{center}
			[@Image;<?=$v["Initials"]; ?>;width=1.5cm;height=0.75cm]
			\end{center}
		    <?php } ?>
	        }}
	        <?php if ($len - $i++ > 1) echo "&\n"; ?>
	    <?php } ?>
    \end{tabular}
}
	    
# [@Size;9] [@Center] <?=$conf["Title"]; ?>

<?php if (!is_array($conf["Content"])) { ?>
    <?=$conf["Content"]; ?>
<?php } else { ?>
    <?php foreach ($conf["Content"] as $txt) { ?>
	<?=$txt; ?>
    <?php } ?>
<?php } ?>

\noindent
\begin{center}
<?php
$signature_count = max(1, count($conf["Signatories"]));
$signature_box_width = 18 / $signature_count - 0.2 * $signature_count;
$signature_image_width = max(1.5, min(4.8, $signature_box_width - 0.5));
?>
\begin{tabular}{<?php foreach ($conf["Signatories"] as $k => $v) echo "@{}c "; ?>@{}}
    \textit{\centering Signature<?=count($conf["Signatories"]) > 1 ? "s" : ""; ?>} \\[0.5em]
    <?php $i = 0; $len = count($conf["Signatories"]); ?>
    <?php foreach ($conf["Signatories"] as $k => $v) { ?>
        \fbox{\parbox[c][3.5cm][c]{<?=$signature_box_width; ?>cm}{
            <?=isset($v["Identity"]) ? $v["Identity"]."\\\\" : ""; ?>
            <?=$v["Role"]; ?>\par
            <?php if (isset($v["Signature"]) && is_string($v["Signature"]) && trim($v["Signature"]) != "") { ?>
                \begin{center}
                [@Image;<?=$v["Signature"]; ?>;width=<?=$signature_image_width; ?>cm;height=2.4cm]
                \end{center}
            <?php } else { ?>
                \vspace{0.35cm}
            <?php } ?>
            \vspace*{\fill}
            }
	}
	<?php if ($len - $i++ > 1) echo "&\n"; ?>
    <?php } ?>
\end{tabular}
\end{center}
    
<?php }
