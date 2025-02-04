<?php

function		BuildDocument(&$conf) {

    $conf[".Engine"] = "latex";
?>

---
geometry: margin=0cm, paperwidth=21cm, paperheight=29.7cm
output: pdf_document
lang: fr-FR
documentclass: article
indent: 2pt
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

<?php if (isset($conf["Footer"]["Left"])) { ?>
    \fancyfoot[L]{<?=$conf["Footer"]["Left"]; ?>}
<?php } ?>
<?php if (isset($conf["Footer"]["Center"])) { ?>
    \fancyfoot[C]{<?=$conf["Footer"]["Center"]; ?>}
<?php } ?>
<?php if (isset($conf["Footer"]["Right"])) { ?>
    \fancyfoot[R]{<?=$conf["Footer"]["Right"]; ?>}
<?php } ?>
<?php if (isset($conf["Footer"]) && is_string($conf["Footer"])) { ?>
    \fancyfoot[]{<?=$conf["Footer"]; ?>}
<?php } ?>


\noindent
<?=str_replace("\n", "\\\n", $conf["From"]); ?>

\makebox[\textwidth][r]{
    \begin{minipage}{8cm}
        <?=str_replace("\n", "\\\\\n", $conf["Target"]); ?>

    \end{minipage}
}

# [@Size;9] [@Center] <?=$conf["Title"]; ?>

\vspace{2cm}

<?php if (!is_array($conf["Content"])) { ?>
    <?=$conf["Content"]; ?>
<?php } else { ?>
    <?php foreach ($conf["Content"] as $txt) { ?>
	<?=$txt; ?>
    <?php } ?>
<?php } ?>

\vspace{2cm}

\begin{flushright}
	<?=$conf["Signature"]; ?>
\end{flushright}
    
<?php }

