<?php

$short_options = "a:i:s:f:o:c:d:m:"; // ActivityFile (Template), Instance information, Optional style
$long_options = [
    "activity:", "instance:", "style:", "format:", "output:", "configuration:", "dictionnary:", "medals:"
];
$bold = "\033[1m";
$normal = "\033[0m";
$description = [
    "The Dabsic file describing the activity you want to generate the document. {$bold}Mandatory{$normal}",
    "The Dabsic file describing the instance of the activity. {$bold}Mandatory{$normal}",
    "The style of the documentation you want to generate. Default is /etc/technocore/default.css",
    "The format of the document you want to generate. (PDF, Web, Schoolbook, Pocketbook). Default is PDF",
    "The output file. Default is stdout",
    "DocBuilder configuration file. Default is /etc/technocore/docbuilder.dab",
    "DocBuilder's dictionnary file. Default is /etc/technocore/dictionnary.dab",
    "Medals directory. Default is /etc/technocore/medals"
];

function Usage()
{
    global $short_options;
    global $long_options;
    global $description;
    global $normal;

    echo "{$normal}Usage is:\n";
    for ($i = 0; isset($short_options[$i * 2]); ++$i)
	fprintf(STDOUT, "\t%-32s %s.\n",
		"-".str_replace(":", "", $short_options[$i * 2])."/--".str_replace(":", "", $long_options[$i]),
		$description[$i]
	);
    return (0);
}

function MustBeAnArray(&$val)
{
    if ($val == NULL)
	$val = [];
    if (is_array($val))
	return ;
    $val = [$val];
}

function Prepare($argv)
{
    global $DocBuilder;
    global $short_options;
    global $long_options;

    $options = getopt($short_options, $long_options);

    $Configuration = "";
    if (isset($options["configuration"]))
	$Configuration = $long_options["configuration"];
    else if (isset($options["c"]))
	$Configuration = $options["c"];
    else
	$Configuration = "/etc/technocore/docbuilder.dab";
    if (!($DocBuilder->Configuration = LoadDabsic($Configuration)))
    {
	fprintf(STDERR, "$argv[0]: Invalid configuration file $Configuration.\n");
	exit(1);
    }

    $Dictionnary = "";
    if (isset($options["dictionnary"]))
	$Dictionnary = $options["dictionnary"];
    else if (isset($options["d"]))
	$Dictionnary = $options["d"];
    else
	$Dictionnary = "/etc/technocore/dictionnary.dab";
    if (!($DocBuilder->Dictionnary = LoadDabsic($Dictionnary)))
    {
	fprintf(STDERR, "$argv[0]: Invalid dictionnary file $Dictionnary.\n");
	exit(1);
    }

    $DocBuilder->MedalsDir = "";
    if (isset($options["medals"]))
	$DocBuilder->MedalsDir = $options["medals"];
    else if (isset($options["m"]))
	$DocBuilder->MedalsDir = $options["m"];
    else
	$DocBuilder->MedalsDir = "/etc/technocore/medals/";

    $DocBuilder->Activity = "";
    if (isset($options["activity"]))
	$DocBuilder->Activity = $options["activity"];
    else if (isset($options["a"]))
	$DocBuilder->Activity = $options["a"];
    if ($DocBuilder->Activity == "")
    {
	fprintf(STDERR, "$argv[0]: Missing parameter: activity configuration (-a file/--activity=file).\n");
	exit(1);
    }
    $ActivityFile = $DocBuilder->Activity;
    if (($DocBuilder->Activity = LoadDabsic($DocBuilder->Activity)) == false)
    {
	fprintf(STDERR, "$argv[0]: Invalid activity configuration $ActivityFile.\n");
	exit(1);
    }
    if (!isset($DocBuilder->Activity["Exercises"]))
    {
	fprintf(STDERR, "$argv[0]: No Exercises node inside $ActivityFile.\n");
	exit(1);
    }
    MustBeAnArray($DocBuilder->Activity["Medals"]);
    foreach ($DocBuilder->Activity["Exercises"] as $i => $j)
    {
	if (is_array($j))
	    MustBeAnArray($DocBuilder->Activity["Exercises"][$i]["Medals"]);
    }

    $DocBuilder->Instance = "";
    if (isset($options["instance"]))
	$DocBuilder->Instance = $options["instance"];
    else if (isset($options["i"]))
	$DocBuilder->Instance = $options["i"];
    if ($DocBuilder->Instance == "")
    {
	fprintf(STDERR, "$argv[0]: Missing parameter: instance configuration (-a file/--activity=file).\n");
	exit(1);
    }
    $InstanceFile = $DocBuilder->Instance;
    if (($DocBuilder->Instance = LoadDabsic($DocBuilder->Instance)) == false)
    {
	fprintf(STDERR, "$argv[0]: Invalid instance configuration $InstanceFile.\n");
	exit(1);
    }
    if (!isset($DocBuilder->Instance["FullName"]) && isset($DocBuilder->Instance["Login"]))
	$DocBuilder->Instance["FullName"] = $DocBuilder->Instance["Login"];
    MustBeAnArray($DocBuilder->Instance["Medals"]);
    MustBeAnArray($DocBuilder->Instance["AcquiredMedals"]);

    $DocBuilder->Style = "";
    if (isset($options["style"]))
	$DocBuilder->Style = $options["style"];
    else if (isset($options["s"]))
	$DocBuilder->Style = $options["s"];
    if ($DocBuilder->Style == "")
    {
	if (!isset($Configuration["DefaultStyle"]))
	    $DocBuilder->Style = "/etc/technocore/default.css";
	else
	    $DocBuilder->Style = $Configuration["DefaultStyle"];
    }
    if (($StyleC = @file_get_contents($DocBuilder->Style)) == NULL)
    {
	$DocBuilder->Warnings[] = sprintf("Invalid style file $DocBuilder->Style: loading failure. Using an empty CSS instead.");
	$StyleC = "";
    }
    if (($DocBuilder->Style = $StyleC) == "")
    {
	$DocBuilder->LineHeight = 0.5;
	$DocBuilder->Warnings[] = "No line-height was specified, as there is not '*' rule in CSS. Setting {$DocBuilder->LineHeight}cm.";
    }
    else
    {
	// On va chercher la hauteur d'une ligne, afin de pouvoir paginer en fonction... En CENTIMETRES SEULEMENT.
	$CSS = new CssParser; // Merci, Peter Kröner.
	$CSS->load_string($DocBuilder->Style);
	$CSS->parse();
	foreach ($CSS->parsed as $cnt)
	{
	    foreach ($cnt as $s => $r)
	    {
		if ($s != "*")
		    continue ;
		foreach ($r as $prop => $val)
		{
		    if ($prop == "line-height" && preg_match("^[0-9]+cm$", $val))
		    {
			$DocBuilder->LineHeight = (int)$val;
			break 3;
		    }
		}
		// Si aucune hauteur de ligne n'a été prévue... on en établie une de force.
		$DocBuilder->LineHeight = 0.5; // Centimetres
		$cnt[$s]["line-height"] = $DocBuilder->LineHeight."cm";
		$DocBuilder->Style = $CSS->glue();
		$DocBuilder->Warnings[] = "No line-height was specified inside the '*' rule of CSS. Setting {$DocBuilder->LineHeight}cm.";
	    }
	}
    }

    $DocBuilder->Format = "";
    if (isset($options["format"]))
	$DocBuilder->Format = $options["format"];
    else if (isset($options["f"]))
	$DocBuilder->Format = $options["f"];
    if ($DocBuilder->Format == "")
    {
	if (!isset($DocBuilder->Configuration["DefaultFormat"]))
	    $DocBuilder->Format = "PDF";
	else
	    $DocBuilder->Format = $DocBuilder->Configuration["DefaultFormat"];
    }
    if ($DocBuilder->Format == "PDF")
	$DocBuilder->PageHeight = 20; // Centimetres. A4 laisse 25 centimètres. Donc 5cm de marge pour titres et cadres...
    else
    {
	fprintf(STDERR, "$argv[0]: Invalid format. The only currently supported format is 'PDF'.\n");
	exit(1);
    }

    if (isset($options["output"]))
	$DocBuilder->OutputFile = $options["output"];
    else if (isset($options["o"]))
	$DocBuilder->OutputFile = $options["o"];
    else
	$DocBuilder->OutputFile = "/dev/stdout";


    if (isset($DocBuilder->Instance["Language"]))
	$DocBuilder->Language = $DocBuilder->Instance["Language"];
    else if (isset($DocBuilder->Activity["Language"]))
	$DocBuilder->Language = $DocBuilder->Activity["Language"];
    else if (isset($DocBuilder->Configuration["Language"]))
	$DocBuilder->Language = $DocBuilder->Configuration["Language"];
    else
	$DocBuilder->Language = "FR"; // Par défaut, c'est FRANCAIS môsieur.

    if (isset($options["no-pretty"]))
	$DocBuilder->Pretty = false;

    $DocBuilder->GlobalMedals = GetGlobalMedals();
    return ($DocBuilder);
}
