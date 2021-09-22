<?php

// Renvoi le mot demandé et signale une erreur si il n'est pas la
function MustPrint(&$cnf, $field, $text = true, $ctx = "Global")
{
    global $DocBuilder;

    if ($DocBuilder->Dictionnary == $cnf)
	return (Translate($field));
    // Si on peut afficher, on affiche. Sinon on enregistre une erreur.
    if (!isset($cnf[$field]))
    {
	$DocBuilder->Errors[] = "$ctx: $field is missing.";
	// On etablit une valeur pour eviter d'écrire plusieurs fois le message d'erreur.
	$cnf[$field] = "";
	return ("");
    }
    if (!$text)
	return ($cnf[$field]);
    return (Translate($cnf[$field]));
}
