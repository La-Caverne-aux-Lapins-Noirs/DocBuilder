<?php

function Paginize($text)
{
    global $DocBuilder;

    // On recupere le texte du chapitre et effectuons un découpage du contenu.
    $text = str_replace("<br />", "<br />!@!", $text);
    $text = str_replace("<br/>", "<br />!@!", $text);
    $text = str_replace("<br>", "<br />!@!", $text);
    $text = str_replace("</p>", "</p>!@!", $text);
    $text = str_replace("</h1>", "</h1>!@!", $text);
    $text = str_replace("</h2>", "</h2>!@!", $text);
    $text = str_replace("</h3>", "</h3>!@!", $text);
    $text = str_replace("</h4>", "</h4>!@!", $text);
    $text = str_replace("</h5>", "</h5>!@!", $text);
    $text = str_replace("</h6>", "</h6>!@!", $text);
    $text = str_replace("</li>", "</li>!@!", $text);
    $text = str_replace("</ul>", "</ul>!@!", $text);
    $text = explode("!@!", $text);
    $NewOut = [];
    foreach ($text as $line)
    {
	if (($line = trim($line)) != "")
	    $NewOut[] = $line;
    }
    $text = $NewOut;

    ////////////////////////////////////////
    // On va maintenant gérer la pagination
    // On commence par démarrer la page...
    OpenPage();
    // Ensuite on déroule toutes les lignes du chapitre.
    for ($line = 0; $line < count($text); )
    {
	$Y = 0;
	// On boucle tant qu'on a pas rempli la page ou terminé les lignes.
	while ($Y < $DocBuilder->PageHeight && $line < count($text))
	{
	    // Si un saut de page est explicitement demandé - Attention, il ne faut pas que des balises soient ouvertes...
	    if (strstr($text[$line], "@PAGEBREAK"))
	    {
		$parts = explode("@PAGEBREAK", $text[$line]);
		echo $parts[0]; // On affiche le texte situe avant le page break sur l'ancienne page
		ClosePage(); // On ferme donc la page courante
		OpenPage(); // On ouvre la nouvelle page
		echo $parts[1]; // On affiche le texte situé apres le page break sur la nouvelle page
		$Y = 0; // On ramène le "curseur" en haut
	    }
	    else
		echo $text[$line]; // Affichage de la ligne
	    $Y += $DocBuilder->LineHeight;
	    echo "<!-- CURSOR: $Y / $DocBuilder->PageHeight -->\n";
	    $line += 1;
	}
	ClosePage(); // Fermeture de la page une fois le curseur descendu.
    }
}
