<?php

function OpenDocument()
{
    global $DocBuilder;

    if ($DocBuilder->Code == "html")
	require (__DIR__."/template/open_document_html.php");
    else if ($DocBuilder->Code == "latex")
	require (__DIR__."/template/open_document_latex.php");
    // Si c'est pas un format listé, ce n'est pas grave, il n'y aura juste pas d'ouverture
}

