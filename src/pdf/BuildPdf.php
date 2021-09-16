<?php

function BuildPdfExercise($ex, $num)
{
    extract($GLOBALS);

    if ($DocBuilder->Format == "PDFA4")
    {
	// Indique qu'il ne faut pas terminer la page après l'exercice.
	if ($ex["NoNewPage"])
	    ;
    }
    else if ($DocBuilder->Format == "PDFA5")
    {
	// Indique qu'il faut mettre fin à la page après l'exercice.
	if ($ex["NewPage"])
	    ;
    }
    
    /*
    // Actions spécifiques de mise en page.
    if ($ex == "Idle")
	return (true);
    if ($ex == "OpenPage")
	return (StartSubRecord(true));
    if ($ex == "ClosePage")
	return (StopSubRecord(true));
    // On enregistre le numéro de page de l'exercice
    $DocBuilder->ExercicePage[Translate(@$ex["Name"])] = $PageCount;
    // On ajoute un chapitre.
    $ChapterCount["Count"] += 1;
    if (!$NewPage)
	// Un chapitre est un "Open" + "Instruction" + "Close".
	// Tout exercice renseigné sans utilisation de OpenPage/ClosePage est un chapitre
	return (AddChapter($ex, $num));
    return (AddInstruction($ex, $num));
     */
}

function BuildPdfBody($exercises, &$depth)
{
    $cnt = 1;
    foreach ($exercises as $ex)
    {
	if (isset($ex["NoDoc"]))
	    continue ;

	// Le tableau dispose de plusieurs dimensions afin
	// de pouvoir chainer des exercices ou actions spécifiques liés.
	// Par exemple: compiler un exercice, puis le tester.
	// Par exemple: verifier la norme d'un exercice, puis le tester.
	// Etc.
	if (IsArray($ex))
	    BuildPdfBody($ex, array_push($depth, $cnt));
	else
	    BuildPdfExercise($ex, array_push($depth, $cnt));
	$cnt += 1;
    }
    return ($cnt);
}

function BuildPdf()
{
    extract($GLOBALS);

    $Depth = [];
    BuildPdfOpening();
    BuildPdfBody($DocBuilder->Activity["Exercises"], $Depth);
    CloseDocument();
}
