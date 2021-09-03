<?php

function BuildFromConfiguration($info, $Configuration)
{
    global $DocBuilder;

    if (isset($Configuration["SideMark"]))
	SideMark($Configuration["SideMark"]);
    else
	echo ReadMarkdown($info["Document"]);
}

function AddInstruction($info, $chapter)
{
    global $DocBuilder;

    //////////////////////////////////////
    // Generation du contenu du chaptitre

    // Afin de faciliter la lecture des sources du PDF
    Separator();
    echo "<!-- Inserting ".Translate($info["Name"])." -->";

    Title($info["Name"], $chapter);

    // La documentation est spécifiée DANS le fichier activité
    if (isset($info["Document"]) && is_array($info["Document"]))
	BuildFromConfiguration($info, $info["Document"]);
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
	else
	    echo ReadMarkdown($info["Document"]);
    }

    $Exercise = $info;
    require (__DIR__."/../template/pdf_local_medals.php");
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

function BrowseExercise(&$ex, &$NewPage, &$ChapterCount)
{
    if ($ex == "Idle") // Pour tester avec un fichier vide...
	return ;
    if ($ex == "OpenPage")
    {
	$NewPage = true;
	ob_start("SubKeepContent");
	OpenPage($ex);
	return ;
    }
    if ($ex == "ClosePage")
    {
	$NewPage = false;
	$SubOutput = $SubOutput.ob_get_contents();
	ob_end_clean();
	Paginize($SubOutput);
	$SubOutput = "";
	return ;
    }
    if (IsArray($ex))
    {
	foreach ($ex as $subex)
	{
	    BrowseExercise($subex, $NewPage, $ChapterCount["Sub"]);
	}
	return ;
    }
    $DocBuilder->ExercicePage[Translate($ex["Name"])] = $PageCount;
    $ChapterCount["Count"] += 1;
    if ($NewPage)
    {
	AddInstruction($ex, $ChapterCount);
	return ;
    }
    AddChapter($ex, $ChapterCount, $Depth);
}

function BuildPdf()
{
    global $DocBuilder;
    global $PageCount;
    global $SubOutput;

    OpenDocument();
    require_once (__DIR__."/../template/pdf_front_page.php");
    AddChapter(["Type" => "Builtin", "Document" => __DIR__."/../template/pdf_summary.php", "Name" => "Index"], [], 0);

    $NewPage = false;
    $Depth = 0;
    $ChapterCount["Count"] = 0;
    $ChapterCount["Sub"] = [];
    foreach ($DocBuilder->Activity["Exercises"] as $ex)
    {
	BrowseExercise($ex, $NewPage, $ChapterCount, $Depth);
    }
    CloseDocument();
}
