<?php

function LoadConfiguration($options)
{
    global $DocBuilder;
    global $Types;
    global $argv;

    $DocBuilder->ConfigurationFile = GetOption($options, "configuration", "c", DOCBUILDER_DEFAULT_CONFIGURATION);
    $DocBuilder->ActivityFile = GetOption($options, "activity", "a", "");
    $DocBuilder->InstanceFile = GetOption($options, "instance", "i", "");

    if (!($DocBuilder->Configuration = LoadDabsic([
	$DocBuilder->ConfigurationFile,
	// Par défaut c'est FRANCAIS mösieur.
	"Language=\"".GetOption($options, "language", "l", "FR")."\"\n",
	$DocBuilder->ActivityFile,
	$DocBuilder->InstanceFile])
    ))
        return (false);

    $DocBuilder->ConfigurationFile = GetOption($options, "configuration", "c", DOCBUILDER_DEFAULT_CONFIGURATION);
    $DocBuilder->ActivityFile = GetOption($options, "activity", "a", "");
    if (!LoadFile($DocBuilder->Dictionnary, $options, "dictionnary", "d", DOCBUILDER_DEFAULT_DICTIONNARY))
	return (false);

    if (isset($DocBuilder->Configuration["Type"]))
	$DocBuilder->Type = $DocBuilder->Configuration["Type"];
    else
	$DocBuilder->Type = DOCBUILDER_DEFAULT_TYPE;
    if (!isset($Types[$DocBuilder->Type]))
        return (false);
    $DocBuilder->Code = $Types[$DocBuilder->Type]["engine"];
    $DocBuilder->PageHeight = $Types[$DocBuilder->Type]["page"][1];
    $DocBuilder->Pager = $Types[$DocBuilder->Type]["pager"];

    $DocBuilder->MedalsDir = GetOption($options, "medals", "m", DOCBUILDER_DEFAULT_MEDALS_DIR);

    $DocBuilder->Configuration["GenerationTime"] = date("d/m/Y H:i:s", time());
    $DocBuilder->Configuration["GenerationDate"] = date("d/m/Y", time());

    return (true);
}
