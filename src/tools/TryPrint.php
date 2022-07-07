<?php

// Renvoi le mot demandé et émet une alerte si il n'est pas la
function TryPrint(&$cnf, $field, $text = true, $ctx = "Global")
{
    global $DocBuilder;

    if ($DocBuilder->Dictionnary == $cnf)
	return (Translate($field));
    // Si on peut afficher, on affiche. Sinon on enregistre une erreur.
    $data = &ResolveAddress($cnf, $field);
    if ($data == NULL)
    {
	if (!is_array($field))
	    $field = [$field];
	$DocBuilder->Warnings[] = "$ctx: ".implode(".", $field)." is missing.";
	// On etablit une valeur pour eviter d'écrire plusieurs fois le message d'erreur.
	return ($data = "");
    }
    if (!$text)
	return ($data);
    return (Translate($data));
}

