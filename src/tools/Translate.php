<?php

// Recherche si le mot dans $field dispose d'une traduction
function Translate($field)
{
    global $DocBuilder;

    // Le champ est un tableau, les textes de langues y sont peut etre directement précisé...
    if (is_array($field))
    {
	// Si la langue courante est précisée dans le champ, alors on l'exploite
	if (isset($field[$DocBuilder->Language]))
	    return ($field[$DocBuilder->Language]);
	// Sinon on retombe sur francais.
	if (isset($field["FR"]))
	{
	    $DocBuilder->Warnings[] = "Field {$field["FR"]} cannot be found in $DocBuilder->Language, fallbacking on FR.";
	    return ($field["FR"]);
	}
	// Sinon on continue... peut-être est ce dans le dictionnaire, ou alors peut-être est ce non traduisible.
    }

    // C'est peut-être que le champ est un identifieur pour le dictionnaire alors?
    // Si ce n'est pas le cas, on l'affiche direct...
    if (preg_match("/^[_a-zA-Z][_a-zA-Z0-9]*$/", $field) == false)
	return ($field);

    // Si c'en est un, alors on regarde si le texte recherché est dispo dans le dico de la langue en cours
    if (isset($DocBuilder->Dictionnary[$DocBuilder->Language][$field]))
	return ($DocBuilder->Dictionnary[$DocBuilder->Language][$field]);
    // Sinon, on regarde en francais ou TOUT est censé etre dispo. 
    if (isset($DocBuilder->Dictionnary["FR"][$field]))
    {
	$DocBuilder->Warnings[] = "Field $field cannot be found in $DocBuilder->Language, fallbacking on FR.";
	return ($DocBuilder->Dictionnary["FR"][$field]);
    }
    // Si rien n'y fait, on renvoit...
    return ($field);
}

