<?php

require_once (__DIR__."/CDocBuilder.php");
require_once (__DIR__."/Markdown.php");
require_once (__DIR__."/Options.php");
require_once (__DIR__."/KeepContent.php");
require_once (__DIR__."/PrintAndExit.php");
require_once (__DIR__."/Globals.php");

function main($argc, array $argv)
{
    if ($argc == 1)
	return (Usage());

    //////////////////////////////////
    // Chargement des éléments requis
    // On récupère les informations des differents fichiers de configuration et de la ligne de commande
    // La configuration de DocBuilder, de l'activité, de l'instance courante (élève, dates, etc.) et le style
    // a utiliser. Le type indique quel est le format de sortie (site web, PDF, livre A4, livre poche, etc.)
    if (Prepare($argv) == false)
	return (1);

    ///////////////////////////////////////
    // Construction du document en mémoire
    extract($GLOBALS);
    ob_start("KeepContent");
    BuildDocument();
    ob_end_flush();

    ////////////////////////////////////////////////////////////////////////////////////
    // Resolve "!@@@nbr@@@!" pattern by setting page numbers, resolve @PAGENUMBER@ also
    BuildSummaryLink();
    //////////////////////////////////////////////////////////////////////////
    // Conclusion de l'execution du programme et envoi du texte sur la sortie
    GenerateOutput();

    return (0);
}

