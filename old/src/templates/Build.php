<?php

function Build()
{
    extract($GLOBALS);

    // On part de rien.
    $DocBuilder->Output = "";

    if (!file_exists(($dire = __DIR__."/".strtolower($DocBuilder->Code))))
    {
	$DocBuilder->Errors[] = "Global: {$DocBuilder->Code} is not a supported document generator.";
	return ;
    }
    if (!file_exists(($dirt = __DIR__."/".strtolower($DocBuilder->Type))))
    {
	$DocBuilder->Errors[] = "Global: {$DocBuilder->Type} is not a supported document type.";
	return ;
    }

    // On commence par ouvrir le document.
    StartBuffer();
    if (file_exists(($file = "$dire/open_document.php")))
	require_once ($file);
    $DocBuilder->Output .= StopBuffer();

    // Ensuite on va le remplir. Pour commencer, on génère le contenu non paginé
    StartBuffer();
    require_once ("$dirt/build.php");
    // On récupère tout ce qui a été généré par le modèle demandé
    $content = StopBuffer();
    // On pagine l'ensemble du texte obtenu
    StartBuffer();
    Paginize($content);
    $DocBuilder->Output .= StopBuffer();

    // On clot maintenant le document
    StartBuffer();
    if (file_exists(($file = "$dire/close_document.php")))
	require_once ($file);
    $DocBuilder->Output .= StopBuffer();
}
