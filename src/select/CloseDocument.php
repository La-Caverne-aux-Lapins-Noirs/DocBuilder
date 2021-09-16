<?php // @codeCoverageIgnoreStart

function CloseDocument()
{
    extract($GLOBALS);

    if ($DocBuilder->Code == "html")
	require (__DIR__."/template/close_document_html.php");
    else if ($DocBuilder->Code == "latex")
	require (__DIR__."/template/close_document_latex.php");
    // Si c'est pas un format listé, ce n'est pas grave, il n'y aura juste pas de fermeture
}

// @codeCoverageIgnoreEnd
