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
    \fancyfoot[L]{
        <?php $w = 21 - 2 - count($conf["Signatories"]) * 3; ?>
        \begin{minipage}[t]{<?=$w; ?>cm}
            <?=$conf["Footer"]; ?>
        \end{minipage}%
        \vspace*{\fill}
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
		    <?php if (isset($v["Signature"])) { ?>
			\begin{center}
			[@Image;<?=$v["Initials"]; ?>;width=1.5cm;height=0.5cm]
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
\begin{tabular}{<?php foreach ($conf["Signatories"] as $k => $v) echo "@{}c "; ?>@{}}
    \textit{\centering Signature<?=count($conf["Signatories"]) > 1 ? "s" : ""; ?>} \\[0.5em]
    <?php $i = 0; $len = count($conf["Signatories"]); ?>
    <?php foreach ($conf["Signatories"] as $k => $v) { ?>
        \fbox{\parbox[c][3.5cm][c]{<?=18 / count($conf["Signatories"]) - 0.2 * count($conf["Signatories"]); ?>cm}{
            <?=isset($v["Identity"]) ? $v["Identity"]."\\\\" : ""; ?>
            <?=$v["Role"]; ?>
            <?php if (isset($v["Signature"])) { ?>
		\begin{center}
		[@Image;<?=$v["Signature"]; ?>;width=4cm;height=2cm]
		\end{center}
            <?php } ?>
            \vspace*{\fill}
            }
	}
	<?php if ($len - $i++ > 1) echo "&\n"; ?>
    <?php } ?>
\end{tabular}
\end{center}
    
<?php }
