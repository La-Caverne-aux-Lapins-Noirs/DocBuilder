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
	    $DocBuilder->Warnings[] = "Field $field cannot be found in $DocBuilder->Language, fallbacking on FR.";
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

// Renvoi le mot demandé et signale une erreur si il n'est pas la
function MustPrint(&$cnf, $field, $text = true, $ctx = "Global")
{
    global $DocBuilder;

    // Si on peut afficher, on affiche. Sinon on enregistre une erreur.
    // Attention: $cnf[$field] peut-être un tableau avec des cases pour chaque langage
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

// Affiche un meta HTML
function PrintMeta($cnf, $name, $cname, $mandatory = false)
{
    if ($mandatory)
	$out = MustPrint($cnf, $cname);
    else
	$out = TryPrint($cnf, $cname);
    if ($out != "")
	echo "<metal name='$name' content='$out' />\n";
}

// Affiche un bout de HTML avec un element précisé, traduisible, a chercher dans la conf
function PrintMarkup($cnf, $name, $html, $text = true, $mandatory = false, $ctx = "Global")
{
    if ($mandatory)
	$out = MustPrint($cnf, $name, $text, $ctx);
    else
	$out = TryPrint($cnf, $name, $text, $ctx);
    if ($out != "")
	echo str_replace("@@", $out, $html)."\n";
}

// Affiche une image ou du HTML a la place
function PrintImage($cnf, $pic, $label, $label_replace = "", $props = "", $mandatory = false, $ctx = "Global")
{
    global $Dictionnary;

    if ($mandatory)
    {
	$out = MustPrint($cnf, $pic, false, $ctx);
	$label = MustPrint($cnf, $label, true, $ctx);
    }
    else
    {
	$out = TryPrint($cnf, $pic, false, $ctx);
	$label = TryPrint($cnf, $label, true, $ctx);
    }
    if ($out != "")
	echo "<img src='$out' alt='$label' $props />\n";
    else if ($label != "" && $label_replace != "")
	echo str_replace("@@", $label, $label_replace);
}

