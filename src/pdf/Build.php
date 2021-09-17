<?php

function BuildPdfExercise($ex, $num)
{
    extract($GLOBALS);

    $Ex = $ex;
    $ChapterCount = MergeNumber($num);

    // Si aucune page n'est ouverte, on en ouvre une.
    if ($DocBuilder->SubRecording == false)
	StartSubRecord();

    // Page blanche
    if ($ex == "Blank")
    {
	StopSubRecord();
	StartSubRecord();
	return ;
    }

    BuildEntry($ex, $num);

    // En fonction du format, on peut fermer la page sous condition.
    if ($DocBuilder->Format == "PDFA4")
    {
	// On achève la page, sauf si l'inverse est demandé
	if (!isset($ex["Document"]["NoNewPage"]))
	    StopSubRecord();
	return ;
    }
    if ($DocBuilder->Format == "PDFA5")
    {
	// On fait une nouvelle page seulement is c'est demandé
	if (isset($ex["Document"]["NewPage"]))
	    StopSubRecord();
	return ;
    }
}

function BuildPdf()
{
    extract($GLOBALS);

    $Depth = [];
    BuildPdfOpening();
    BuildPdfBody($DocBuilder->Activity["Exercises"], $Depth);
    CloseDocument();
}
