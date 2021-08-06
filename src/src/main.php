<?php
//@define("ACTIVITY_DIRECTORY", "/etc/technocore/activities/");

require_once (__DIR__."/../tools/index.php");
require_once (__DIR__."/../deps/index.php");
require_once (__DIR__."/Prepare.php");
require_once (__DIR__."/BuildDocument.php");
require_once (__DIR__."/BuildSummaryLink.php");
require_once (__DIR__."/GenerateOutput.php");

// Une unique structure contenant tout, pour ne pas avoir à ballader 15 paramètres...
class CDocBuilder
{
    public $Output = "";
    public $OutputFile = "";
    public $Warnings = [];
    public $Errors = [];

    public $Language = "";
    public $Configuration = [];
    public $Dictionnary = [];
    public $Activity = [];
    public $Instance = [];
    public $Style = "";
    public $Type = "";

    public $GlobalMedals = [];
    public $MedalsDir = "";
    public $ChapterCount = 0;
    public $GivenMedals = [];

    public $ExercicePage = [];

    public $PageHeight = 0;
    public $LineHeight = 0;

    public $Pretty = true;
};

$DocBuilder = new CDocBuilder;
$MarkDown = new Parsedown();
$MarkDown->setMarkupEscaped(true);
$MarkDown->setBreaksEnabled(true);
//$MarkDown->setUrlsLinked(false);
$PageCount = 1;
$SubOutput = "";

function KeepContent($buf)
{
    global $DocBuilder;

    $DocBuilder->Output .= $buf;
    return ("");
}

function main($argc, array $argv)
{
    global $DocBuilder;

    if ($argc == 1)
	return (Usage());

    //////////////////////////////////
    // Chargement des éléments requis
    // On récupère les informations des differents fichiers de configuration et de la ligne de commande
    // La configuration de DocBuilder, de l'activité, de l'instance courante (élève, dates, etc.) et le style
    // a utiliser. Le type indique quel est le format de sortie (site web, PDF, livre A4, livre poche, etc.)
    Prepare($argv);

    ///////////////////////////////////////
    // Construction du document en mémoire
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

