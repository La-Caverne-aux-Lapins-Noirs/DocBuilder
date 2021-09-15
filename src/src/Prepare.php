<?php

require_once (__DIR__."/CompleteStyle.php");

function Prepare($options)
{
    extract($GLOBALS);

    $DocBuilder->Code = GetOption($options, "engine", "e", "html");

    if (!isset($DocBuilder->Instance["FullName"]) && isset($DocBuilder->Instance["Login"]))
	$DocBuilder->Instance["FullName"] = $DocBuilder->Instance["Login"];

    if (!isset($DocBuilder->Instance["AcquiredMedals"]))
	$DocBuilder->Instance["AcquiredMedals"] = [];
    else
	$DocBuilder->Instance["AcquiredMedals"] = MustBeAnArray($DocBuilder->Instance["AcquiredMedals"]);

    $confdir = dirname(GetOption($options, "configuration", "c", DOCBUILDER_DEFAULT_CONFIGURATION));
    $DocBuilder->LineHeight = 0.5; // Valeur par défaut.
    $DocBuilder->Style = "";
    $StyleFile = DOCBUILDER_DEFAULT_STYLE;
    if (isset($DocBuilder->Configuration["Style"]))
    {
	$Style = $DocBuilder->Configuration["Style"];

	// Est ce qu'on a du CSS directement ou pas ?
	if (strstr($Style, "{") === false)
	{
	    // Non, alors c'est probablement un chemin.
	    // On ajoute la position du fichier de configuration qui le specifie
	    if (substr($Style, 0, 1) != "/")
		$StyleFile = $confdir."/".$Style;
	    else
		$StyleFile = $Style;

	    // On a un probleme....
	    if (!file_exists($StyleFile))
	    {
		echo sprintf("$argv[0]: Specified file {$StyleFile} for style does not exist.\n");
		return (false);
	    }
	}
	else
	    $DocBuilder->Style = MergeData($Style);
    }

    if (isset($DocBuilder->Configuration["Format"]))
	$Format = $DocBuilder->Configuration["Format"];
    else
	$Format = DOCBUILDER_DEFAULT_FORMAT;
    $DocBuilder->Format = GetOption($options, "format", "f", $Format);
    if ($DocBuilder->Format == "PDFA4")
	$DocBuilder->PageHeight = 20; // 20 Centimètres de contenu sur 29. Le reste est pour le cadre.
    else if ($DocBuilder->Format == "PDFA5")
	$DocBuilder->PageHeight = 17.5; // 21 - 3.5. 1cm5 en haut, 2cm en bas (pour le numero de page)

    if ($DocBuilder->Code == "html")
    {
	// On va recherche ou definir la specification de la hauteur d'une ligne.
	$CSS = new CssParser; // Merci a l'auteur de cette lib.
	// On charge le CSS par defaut de DocBuilder
	$CSS->load_string(file_get_contents(__DIR__."/../style/default.css"));
	if ($DocBuilder->Format == "PDFA4")
	    $CSS->load_string(file_get_contents(__DIR__."/../style/big_pdf.css"));
	else if ($DocBuilder->Format == "PDFA5")
	    $CSS->load_string(file_get_contents(__DIR__."/../style/small_pdf.css"));

	// On charge le CSS indiqué directement dans le fichier de conf
	if ($DocBuilder->Style != "")
	    $CSS->load_string($DocBuilder->Style);
	// Ou on charge le CSS qui est dans le fichier indiqué dans la conf
	else if ($StyleFile != "")
	{
	    $CSS->load_string(file_get_contents($StyleFile));
	    $StyleFile = NULL;
	}

	// On charge aussi le CSS indiqué dans la ligne de commande
	if (LoadFile($DocBuilder->Style, $options, "style", "s", $StyleFile, false) != NULL)
	    $CSS->load_string($DocBuilder->Style);

	// On parse le tout
	$CSS->parse();
	// On rassemble tout dans un seul et meme fichier
	// Afin d'avoir les propriétés qui se mélangent.
	$css = $CSS->glue();
	$CSS = new CssParser();
	$CSS->load_string($css);
	$CSS->parse();
	CompleteStyle($CSS);
    }

    $DocBuilder->OutputFile = GetOption($options, "output", "o", "/dev/stdout");

    if (isset($DocBuilder->Instance["Language"]))
	$DocBuilder->Language = $DocBuilder->Instance["Language"];
    else if (isset($DocBuilder->Activity["Language"]))
	$DocBuilder->Language = $DocBuilder->Activity["Language"];
    else if (isset($DocBuilder->Configuration["Language"]))
	$DocBuilder->Language = $DocBuilder->Configuration["Language"];
    else
	// C'est un logiciel francais, donc par défaut c'est francais.
	$DocBuilder->Language = GetOption($options, "language", "l", "FR");

    if (isset($options["no-pretty"]))
	$DocBuilder->Pretty = false;
    if (isset($options["keep-trace"]))
	$DocBuilder->KeepTrace = true;
    if (isset($options["small-opening"]))
	$DocBuilder->SmallOpening = true;

    $DocBuilder->GlobalMedals = GetGlobalMedals();
    return (true);
}
