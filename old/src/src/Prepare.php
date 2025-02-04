<?php

require_once (__DIR__."/CompleteStyle.php");

function Prepare($options)
{
    extract($GLOBALS);

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
	    if (substr($Style, 0, 1) != "/" && 0) // Non, c'est une mauvaise idée ?
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

    if ($DocBuilder->Code == "html")
    {
	// On va recherche ou definir la specification de la hauteur d'une ligne.
	$CSS = new CssParser; // Merci a l'auteur de cette lib.
	// On charge le CSS par defaut de DocBuilder
	$CSS->load_string(file_get_contents(__DIR__."/../templates/style.css"));
	if (file_exists($st = __DIR__."/../templates/".strtolower($DocBuilder->Pager)."/style.css"))
	    $CSS->load_string(file_get_contents($st));

	// On charge le CSS indiqué directement dans le fichier de conf
	if ($DocBuilder->Style != "")
	    $CSS->load_string($DocBuilder->Style);
	// Ou on charge le CSS qui est dans le fichier indiqué dans la conf
	else if ($StyleFile != "")
	{
	    $CSS->load_string(file_get_contents($StyleFile));
	    $StyleFile = NULL;
	}

	// Pour éviter l'affichage du message d'erreur car -s est optionnelle
	if ($StyleFile == NULL)
	    $StyleFile = "";
	// On charge aussi le CSS indiqué dans la ligne de commande
	if (LoadFile($DocBuilder->Style, $options, "style", "s", $StyleFile, false) != false)
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

    if (isset($DocBuilder->Configuration["Language"]))
	$DocBuilder->Language = $DocBuilder->Configuration["Language"];
    else
	// C'est un logiciel francais, donc par défaut c'est francais, non mais.
	$DocBuilder->Language = GetOption($options, "language", "l", "FR");

    if (isset($options["no-pretty"]))
	$DocBuilder->Pretty = false;
    if (isset($options["keep-trace"]))
	$DocBuilder->KeepTrace = true;
    if (isset($options["small-opening"]))
	$DocBuilder->SmallOpening = true;

    DevelopPath(
	dirname($DocBuilder->ConfigurationFile), $DocBuilder->Configuration, [
	    ["Company", "Logo"], ["Company", "SmallLogo"]
    ]);

    if (!isset($DocBuilder->Configuration["FullName"]) && isset($DocBuilder->Configuration["Login"]))
	$DocBuilder->Configuration["FullName"] = $DocBuilder->Configuration["Login"];
    if (!isset($DocBuilder->Configuration["AcquiredMedals"]))
	$DocBuilder->Configuration["AcquiredMedals"] = [];
    else
	$DocBuilder->Configuration["AcquiredMedals"] = MustBeAnArray($DocBuilder->Configuration["AcquiredMedals"]);
    $DocBuilder->GlobalMedals = GetGlobalMedals();

    DevelopPath(
	dirname($DocBuilder->ActivityFile), $DocBuilder->Configuration, [
	    ["Matter", "Logo"],
	    ["Matter", "SmallLogo"],
	    ["Activity", "Logo"],
	    ["Activity", "SmallLogo"]
    ]);

    // Valeurs par défauts repiqués depuis un autre champ
    if (!isset($DocBuilder->Configuration["Company"]["LegalAddress"]))
	$DocBuilder->Configuration["Company"]["LegalAddress"] = $DocBuilder->Configuration["Company"]["Address"];
    if (!isset($DocBuilder->Configuration["Company"]["LegalCity"]))
	$DocBuilder->Configuration["Company"]["LegalCity"] = $DocBuilder->Configuration["Company"]["City"];

    // Il faudra probablement rajouter les images contenus DANS les fichiers.

    return (true);
}
