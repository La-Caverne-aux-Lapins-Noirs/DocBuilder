<?php

// Renvoi le mot demandé et émet une alerte si il n'est pas la
function TryPrint(&$cnf, $field, $text = true, $ctx = "Global")
{
    global $DocBuilder;

    // Si on peut afficher, on affiche. Sinon on enregistre une erreur.
    // Attention: $cnf[$field] peut-être un tableau avec des cases pour chaque langage
    if (!isset($cnf[$field]))
    {
	$DocBuilder->Warnings[] = "$ctx: $field is missing.";
	// On etablit une valeur pour eviter d'écrire plusieurs fois le message d'erreur.
	$cnf[$field] = "";
	return ("");
    }
    if (!$text)
	return ($cnf[$field]);
    return (Translate($cnf[$field]));
}