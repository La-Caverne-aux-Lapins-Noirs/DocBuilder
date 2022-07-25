<?php

function Paginize($text)
{
    global $DocBuilder;
    global $Page;

    if ($DocBuilder->Code == "latex")
    {
	echo str_replace("@PAGEBREAK", "\\newpage", $text);
	return ;
    }
    if ($DocBuilder->Code != "html")
	return ;

    $Page = 1;

    // On éclate le contenu HTML
    $text = SplitContent($text);
    // On ouvre la première page
    OpenPage();

    // Ensuite on déroule toutes les lignes
    for ($line = 0; $line < count($text); $line += 1)
    {
	// On éclate par demande de saut de ligne. Si il n'y en a pas, le tableau fait une seule case.
	$parts = explode("@PAGEBREAK", $text[$line]);
	for ($i = 0; $i < count($parts) - 1; ++$i)
	{
	    echo $parts[$i];	// On affiche le texte situe avant le page break sur l'ancienne page
	    ClosePage();	// On ferme donc la page courante
	    $Page += 1;		// On change de numéro de page
	    OpenPage();		// On ouvre la nouvelle page
	}
	echo $parts[$i]; // On affiche la partie situé après le dernier page break, ou la seule case
    }

    // On ferme la dernière page
    ClosePage();
}
