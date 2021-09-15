<?php

function LoadConfiguration($options)
{
    global $DocBuilder;

    if (!LoadFile($DocBuilder->Configuration, $options, "configuration", "c", DOCBUILDER_DEFAULT_CONFIGURATION))
	return (false);
    if (!LoadFile($DocBuilder->Dictionnary, $options, "dictionnary", "d", DOCBUILDER_DEFAULT_DICTIONNARY))
	return (false);
    $DocBuilder->MedalsDir = GetOption($options, "medals", "m", DOCBUILDER_DEFAULT_MEDALS_DIR);
    if (!LoadFile($DocBuilder->Activity, $options, "activity", "a"))
	return (false);
    if (!LoadFile($DocBuilder->Instance, $options, "instance", "i"))
	return (false);

    return (true);
}
