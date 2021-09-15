<?php

function Paginize($text)
{
    global $DocBuilder;

    if ($DocBuilder->Code == "latex")
    {
	$text = str_replace("@PAGEBREAK", "\newpage", $text);
	return ;
    }
    else if ($DocBuilder->Code != "html")
	return ;

    // On éclate le contenu HTML
    $text = SplitContent($text);

    // On ouvre une première page.
    OpenPage();
    $open = true;

    // Ensuite on déroule toutes les lignes du chapitre.
    for ($line = 0; $line < count($text); )
    {
	$Y = 0;

	// On boucle tant qu'on a pas rempli la page ou terminé les lignes.
	while ($Y + $DocBuilder->LineHeight < $DocBuilder->PageHeight && $line < count($text))
	{
	    // Si un saut de page est explicitement demandé
	    // Attention, il ne faut pas que des balises soient ouvertes...
	    if ($open == false)
	    {
		OpenPage();
		$open = true;
	    }

	    if (strstr($text[$line], "@PAGEBREAK"))
	    {
		$parts = explode("@PAGEBREAK", $text[$line]);
		for ($i = 0; $i < count($parts) - 1; ++$i)
		{
		    echo $parts[$i]; // On affiche le texte situe avant le page break sur l'ancienne page
		    ClosePage(); // On ferme donc la page courante
		    OpenPage(); // On ouvre la nouvelle page
		}
		echo $parts[$i];
		$Y = 0; // On ramène le "curseur" en haut
	    }
	    else
	    {
		echo $text[$line]; // Affichage de la ligne entière.
	    }

	    $Y += $DocBuilder->LineHeight;
	    echo "<!-- CURSOR: $Y / $DocBuilder->PageHeight -->\n";
	    $line += 1;
	}

	ClosePage();
	$open = false;
    }
}
