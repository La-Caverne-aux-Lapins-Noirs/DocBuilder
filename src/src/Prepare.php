<?php

function GetOption($opt, $long, $short, $default = NULL)
{
    if (isset($opt[$long]))
	return ($opt[$long]);
    if (isset($opt[$short]))
	return ($opt[$short]);
    return ($default);
}

function LoadFile(&$out, $opts, $long, $short, $default = NULL, $dabsic = true)
{
    global $argv;

    if (($File = GetOption($opts, $long, $short, $default)) === NULL)
    {
	echo sprintf("$argv[0]: Missing parameter: $long configuration (-$short file / --$long=file).\n");
	return (false);
    }
    if ($dabsic)
    {
	if (!($out = LoadDabsic($File)))
	{
	    echo sprintf("$argv[0]: Invalid $long file $File.\n");
	    return (false);
	}
    }
    else
    {
	if (!($out = @file_get_contents($File)))
	{
	    echo sprintf("$argv[0]: Invalid $long file $File.\n");
	    return (false);
	}
    }
    return (true);
}

function Prepare($options)
{
    extract($GLOBALS);

    if (!LoadFile($DocBuilder->Configuration, $options, "configuration", "c", DOCBUILDER_DEFAULT_CONFIGURATION))
	return (false);
    if (!LoadFile($DocBuilder->Dictionnary, $options, "dictionnary", "d", DOCBUILDER_DEFAULT_DICTIONNARY))
	return (false);
    $DocBuilder->MedalsDir = GetOption($options, "medals", "m", DOCBUILDER_DEFAULT_MEDALS_DIR);
    if (!LoadFile($DocBuilder->Activity, $options, "activity", "a"))
	return (false);
    if (!LoadFile($DocBuilder->Instance, $options, "instance", "i"))
	return (false);

    if (!isset($DocBuiler->Instance["FullName"]) && isset($DocBuilder->Instance["Login"]))
	$DocBuilder->Instance["FullName"] = $DocBuilder->Instance["Login"];
    if (!isset($DocBuilder->Instance["Medals"]))
	$DocBuilder->Instance["Medals"] = [];
    else
	MustBeAnArray($DocBuilder->Instance["Medals"]);
    if (!isset($DocBuilder->Instance["AcquiredMedals"]))
	$DocBuilder->Instance["AcquiredMedals"] = [];
    else
	MustBeAnArray($DocBuilder->Instance["AcquiredMedals"]);

    $extern = true;
    $DocBuilder->LineHeight = 0.5; // Valeur par défaut.
    $Style = DOCBUILDER_DEFAULT_STYLE;
    if (isset($DocBuilder->Configuration["Style"]))
    {
	// Le champ Style de la configuration peut etre un fichier OU une donnée directement
	$Style = $DocBuilder->Configuration["Style"];
	if (!file_exists($Style))
	    $extern = false;
    }

    if ($extern == false) // On a du CSS directement dans le champ!
	$DocBuilder->Style = MergeData($Style);
    // On a un fichier a charger...
    else if (!LoadFile($DocBuilder->Style, $options, "style", "s", $Style, false))
	return (false);

    // On charge le CSS.
    $CSS = new CssParser; // Merci.
    $CSS->load_string($DocBuilder->Style);
    $CSS->parse();
    foreach ($CSS->parsed as &$cnt)
    {
	foreach ($cnt as $s => &$r)
	{
	    if ($s != "*")
		continue ;
	    foreach ($r as $prop => &$val)
	    {
		if ($prop == "line-height" && prg_match("^[0-9]+cm$", $val))
		{
		    $DocBuilder->LineHeight = (float)$val;
		    break 3;
		}
	    }
	    // Si aucune hauteur de ligne n'a été prévue... on en établie une de force.
	    $r["line-height"] = $DocBuilder->LineHeight."cm";
	    $DocBuilder->Style = $CSS->glue();
	    $DocBuilder->Warnings[] = "No line-height was specified inside the '*' rule of CSS. ".
				      "Setting {$DocBuilder->LineHeight}cm."
				      ;
	}
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

    $DocBuilder->OutputFile = GetOption($options, "output", "o", "/dev/stdout");

    if (isset($DocBuilder->Instance["Language"]))
	$DocBuilder->Language = $DocBuilder->Instance["Language"];
    else if (isset($DocBuilder->Activity["Language"]))
	$DocBuilder->Language = $DocBuilder->Activity["Language"];
    else if (isset($DocBuilder->Configuration["Language"]))
	$DocBuilder->Language = $DocBuilder->Configuration["Language"];
    else
	$DocBuilder->Language = "FR"; // C'est un logiciel francais, donc par défaut c'est francais.

    $DocBuilder->Code = GetOption($options, "engine", "e", "html");

    if (isset($options["no-pretty"]))
	$DocBuilder->Pretty = false;
    if (isset($options["keep-trace"]))
	$DocBuilder->KeepTrace = true;
    if (isset($options["small-opening"]))
	$DocBuilder->SmallOpening = true;

    $DocBuilder->GlobalMedals = GetGlobalMedals();
    return ($DocBuilder);
}
