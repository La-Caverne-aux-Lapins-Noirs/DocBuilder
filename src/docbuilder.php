<?php
require_once (__DIR__."/tools/index.php");
require_once (__DIR__."/deps/index.php");
require_once (__DIR__."/src/index.php");
require_once (__DIR__."/templates/index.php");

$MarkDown = GetMarkdown();

function main($argc, array $argv)
{
    if ($argc == 1)
	return (Usage());

    //////////////////////////////////
    // Chargement des éléments requis
    // On récupère les informations des differents fichiers de configuration et de la ligne de commande
    // La configuration de DocBuilder, de l'activité, de l'instance courante (élève, dates, etc.) et le style
    // a utiliser. Le type indique quel est le format de sortie (site web, PDF, livre A4, livre poche, etc.)
    global $Options;

    $options = getopt($Options->ShortOptions, $Options->LongOptions);
    if (LoadConfiguration($options) == false)
	return (1);
    if (Prepare($options) == false)
	return (1);

    ///////////////////////////////////////
    // Construction du document en mémoire
    extract($GLOBALS);
    ob_start("KeepContent");
    Build();
    ob_end_flush();

    ////////////////////////////////////////////////////////////////////////////////////
    // Resolve "!@@@nbr@@@!" pattern by setting page numbers, resolve @PAGENUMBER@ also
    PostProcess();
    //////////////////////////////////////////////////////////////////////////
    // Conclusion de l'execution du programme et envoi du texte sur la sortie
    GenerateOutput();

    return (0);
}

exit(main(count($argv), $argv));
