<?php
require_once (__DIR__."/RetrieveMedal.php");

function GetLocalMedals($array)
{
    $out = [];
    foreach ($array as $ex)
    {
	if (isset($ex["Medals"]))
	{
	    foreach ($ex["Medals"] as $med)
	    {
		if (!isset($out[$med]))
		    $out[$med] = 0;
		$out[$med] += 1;
	    }
	}
	else if (IsArray($ex))
	    $out = array_merge($out, GetLocalMedals($ex));
    }
    return ($out);
}

function GetGlobalMedals()
{
    global $DocBuilder;

    // On fait la somme des médailles de tous les exercices
    $Medals = GetLocalMedals($DocBuilder->Activity["Exercises"]);

    // On ajoute les médailles de l'exercice complet
    if (isset($DocBuilder->Activity["Medals"]))
    {
	foreach ($DocBuilder->Activity["Medals"] as $med)
	{
	    if (!isset($Medals[$med]))
		$Medals[$med] = 0;
	    $Medals[$med] += 1;
	}
    }

    // On ajoute eventuellement les médailles ajoutés a la main sur l'intranet
    if (isset($DocBuilder->Instance["Medals"]))
    {
	foreach ($DocBuilder->Instance["Medals"] as $med)
	{
	    if (!isset($Medals[$med]))
		$Medals[$med] = 0;
	    $Medals[$med] += 1;
	}
    }

    // On enlève les médailles qu'on a deja du sujet...
    if (isset($DocBuilder->Instance["AcquiredMedals"]))
    {
	foreach ($DocBuilder->Instance["AcquiredMedals"] as $med)
	{
	    if (isset($Medals[$med]) && $Medals[$med] > 0)
		if (($Medals[$med] -= 1) <= 0)
		    unset($Medals[$med]);
	}
    }

    foreach ($Medals as $i => $m)
    {
	$Medals[$i] = RetrieveMedal($i);
    }

    // Voila donc la liste complète des médailles qu'on peut acquérir sur
    // l'activité, en plus de celle qu'on a deja.
    return ($Medals);
}

