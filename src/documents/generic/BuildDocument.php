<?php

function BuildDocument(&$conf)
{
    $conf[".Engine"] = "latex";
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

<?php if (isset($conf["Header"]["Left"])) { ?>
\fancyhead[L]{<?=$conf["Header"]["Left"]; ?>}
<?php } ?>
<?php if (isset($conf["Header"]["Center"])) { ?>
\fancyhead[C]{<?=$conf["Header"]["Center"]; ?>}
<?php } ?>
<?php if (isset($conf["Header"]["Right"])) { ?>
\fancyhead[R]{\raisebox{-0.35cm}{<?=$conf["Header"]["Right"]; ?>}}
<?php } ?>
<?php if (isset($conf["Header"]) && is_string($conf["Header"])) { ?>
\fancyhead[]{<?=$conf["Header"]; ?>}
<?php } ?>

<?php if (isset($conf["Footer"]) && is_string($conf["Footer"])) { ?>
\fancyfoot[L]{%
    \begin{minipage}[t]{\textwidth}
        \raggedright <?=$conf["Footer"]; ?>
    \end{minipage}%
}
<?php } else { ?>
    <?php if (isset($conf["Footer"]["Left"])) { ?>
\fancyfoot[L]{<?=$conf["Footer"]["Left"]; ?>}
    <?php } ?>
    <?php if (isset($conf["Footer"]["Center"])) { ?>
\fancyfoot[C]{<?=$conf["Footer"]["Center"]; ?>}
    <?php } ?>
    <?php if (isset($conf["Footer"]["Right"])) { ?>
\fancyfoot[R]{<?=$conf["Footer"]["Right"]; ?>}
    <?php } ?>
<?php } ?>

<?php if (isset($conf["Content"])) { ?>
    <?php if (is_array($conf["Content"])) { ?>
        <?php foreach ($conf["Content"] as $content) { ?>
<?=$content; ?>
        <?php } ?>
    <?php } else { ?>
<?=$conf["Content"]; ?>
    <?php } ?>
<?php } ?>

<?php
}
