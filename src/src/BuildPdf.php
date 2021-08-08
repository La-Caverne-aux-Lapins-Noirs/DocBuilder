<?php

function Separator()
{
    echo "<!-- ============================================== -->\n";
}

function PdfOpenDocument()
{
    global $DocBuilder;

    require ("template/pdf_open_document.php");
}

function PdfCloseDocument()
{
    global $DocBuilder;

    require ("template/pdf_close_document.php");
}

function PdfOpenPage()
{
    global $DocBuilder;

    require ("template/pdf_page_top.php");
}

function PdfClosePage()
{
    global $DocBuilder;
    global $PageCount;

    require ("template/pdf_page_bottom.php");
}

function SubKeepContent($buf)
{
    global $SubOutput;

    $SubOutput .= $buf;
    return ("");
}

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
    PdfOpenPage();
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
		PdfClosePage(); // On ferme donc la page courante
		PdfOpenPage(); // On ouvre la nouvelle page
		echo $parts[1]; // On affiche le texte situé apres le page break sur la nouvelle page
		$Y = 0; // On ramène le "curseur" en haut
	    }
	    else
		echo $text[$line]; // Affichage de la ligne
	    $Y += $DocBuilder->LineHeight;
	    echo "<!-- CURSOR: $Y / $DocBuilder->PageHeight -->\n";
	    $line += 1;
	}
	PdfClosePage(); // Fermeture de la page une fois le curseur descendu.
    }
}

function PdfTitle($info, $chapter)
{
    if ($chapter == 0)
	echo "<h2 id='index$chapter' class='chapter_title'>".Translate($info["Name"])."</h2>";
    else
	echo "<h2 id='index$chapter' class='chapter_title'>".ToRoman($chapter)." - ".Translate($info["Name"])."</h2>";
}

function PdfSideMark($info)
{
    global $MarkDown;
    global $DocBuilder;

    if (is_array($info))
	$text = implode("", $info);
    else
	$text = $info;
    $Md = "<div class=\"sidemark\">";
    $Md .= $MarkDown->line($text);
    $Md .= "</div>";
    echo $Md;
    $DocBuilder->Warnings[] = "Sidemark are not supported with PDF format (wkHTMLtoPDF issue).";
}

function BuildFromConfiguration($info, $Configuration)
{
    global $DocBuilder;
    global $MarkDown;

    if (isset($Configuration["SideMark"]))
	PdfSideMark($Configuration["SideMark"]);
    if (($type = $info["Type"]) == "Function")
    {
	require ("./template/pdf_function.php");
    }
}

function AddInstruction($info, $chapter)
{
    global $DocBuilder;
    global $MarkDown;

    //////////////////////////////////////
    // Generation du contenu du chaptitre

    // Afin de faciliter la lecture des sources du PDF
    Separator();
    echo "<!-- Inserting ".Translate($info["Name"])." -->";

    PdfTitle($info, $chapter);

    // La documentation est spécifiée DANS le fichier activité
    if (isset($info["Document"]) && is_array($info["Document"]))
    {
	BuildFromConfiguration($info, $info["Document"]);
    }
    // C'est un fichier exterieur. On l'inclu.
    else
    {
	$ext = pathinfo($info["Document"], PATHINFO_EXTENSION);
	// Si le fichier est deja un format a sortie SGML
	if ($ext == "htm" || $ext == "php" || $ext == "html")
	{
	    if (!include($info["Document"]))
	    {
		$DocBuilder->Errors[] = "Cannot include file {$info["Template"]}.";
		return ;
	    }
	}
	// Si le fichier est un element de configuration dont il faut générer la sortie
	else if ($ext == "md")
	{
	    $Md = '<div class="markdown">';
	    $Md .= $MarkDown->text(file_get_contents($info["Document"]));
	    $Md .= '</div>';
	    /*
	    ** BEAUCOUP DE CHOSES A DIRE LA DESSUS!
	     ** Il faut relire le fonctionnement de la doc
	     ** car il semble stupide de reserver le markdown a l'exterieur.
	     ** 100 % des textes devraient être en mardown et augmenté de capacités
	     ** de mise en forme specifique au besoin de DocBuilder.
	     ** Il faut retirer le champ Prototype pour lui préférer du texte de masse
	     ** par l'entremise des nouvelles capacités de templating de Dabsic
	    */
	    preg_replace("/@CODE\s\s+\<blockquote>/", "<blockquote class=\"code\">", $Md);
	    preg_replace("/@CLI\s\s+\<blockquote>/", "<blockquote class=\"cli\">", $Md);
	    preg_replace("/@HINT\s\s+\<blockquote>/", "<blockquote class=\"hint\">", $Md);
	    preg_replace("/@WARNING\s\s+\<blockquote>/", "<blockquote class=\"warning\">", $Md);
	    echo $Md;
	}
	else if ($ext == "json" || $ext == "ini" || $ext == "dab")
	{
	    $json = LoadDabsic($info["Document"]);
	    BuildFromConfiguration($info, $json);
	}
    }

    $Exercise = $info;
    require ("./template/pdf_local_medals.php");
}

function AddChapter($info, $chapter)
{
    global $SubOutput;

    // On lance la bufferisation
    ob_start("SubKeepContent");
    // On ajoute le chapitre
    AddInstruction($info, $chapter);
    // On récupère ce qui a été écrit et on efface le tamon
    ob_end_flush();
    // On organise la sortie au format
    Paginize($SubOutput);
    $SubOutput = "";
}

function BuildPdf()
{
    global $DocBuilder;
    global $PageCount;
    global $SubOutput;

    PdfOpenDocument();
    require_once ("./template/pdf_front_page.php");
    AddChapter(["Type" => "Builtin", "Document" => "./template/pdf_summary.php", "Name" => "Index"], 0);

    $NewPage = false;
    $ChapterCount = 0;
    foreach ($DocBuilder->Activity["Exercises"] as $ex)
    {
	if ($ex == "Idle") // Pour tester avec un fichier vide...
	    continue ;
	if ($ex == "OpenPage")
	{
	    $NewPage = true;
	    ob_start("SubKeepContent");
	    PdfOpenPage($info);
	    continue ;
	}
	if ($ex == "ClosePage")
	{
	    $NewPage = false;
	    $SubOutput = $SubOutput.ob_get_contents();
	    ob_end_clean();
	    Paginize($SubOutput);
	    $SubOutput = "";
	    continue ;
	}
	$DocBuilder->ExercicePage[Translate($ex["Name"])] = $PageCount;
	$ChapterCount += 1;
	if ($NewPage)
	{
	    AddInstruction($ex, $ChapterCount);
	    continue ;
	}
	AddChapter($ex, $ChapterCount);
    }
    PdfCloseDocument();
}
