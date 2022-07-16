<?php

function PostProcess()
{
    extract($GLOBALS);

    foreach ($DocBuilder->ExercicePage as $name => $page)
    {
	$DocBuilder->Output = str_replace("!@@@$name@@@!", $page, $DocBuilder->Output);
    }

    // On remplace le nombre de page
    $DocBuilder->Output = str_replace("@PAGECOUNT@", "$Page", $DocBuilder->Output);
    $DocBuilder->Output = str_replace("@ALINEA@", "<span style='text-indent: 2em; display: inline-block;'>&nbsp;</span>", $DocBuilder->Output);

    // Calcule la position de la page dans le fil de page
    $Doc = explode("@@PAGEPOSITION", $DocBuilder->Output);
    $Generated = [];
    for ($i = 0; $i < count($Doc); ++$i)
    {
	$Generated[] = $Doc[$i];
	if ($i + 1 < count($Doc))
	    $Generated[] = $i * $DocBuilder->PageHeight;
    }
    $DocBuilder->Output = implode($Generated);

    // Ne garde que le dernier element
    $Doc = explode("@@KEEPLAST", $DocBuilder->Output);
    $Generated = [];
    for ($i = 0; $i < count($Doc); ++$i)
    {
	if (substr($Doc[$i], 0, 1) == "[")
	{
	    if ($i + 2 == count($Doc)) // Si on est sur le dernier @@KEEPLAST[
		$Generated[] = substr($Doc[$i], 1);
	    // Si on est pas sur le dernier, on ne colle pas le morceau
	}
	else if (substr($Doc[$i], 0, 1) == "]")
	    $Generated[] = substr($Doc[$i], 1);
	else
	    $Generated[] = $Doc[$i];
    }
    $DocBuilder->Output = implode($Generated);

    // Parceque le parseur de CSS n'est pas un parseur LL mais utile explode de manière naive.
    $DocBuilder->Output = str_replace(
	"url(data:font/woff2;base64,DocumentBuilderFont)",
	"",
	$DocBuilder->Output
    );

    if (strlen(@$DocBuilder->Configuration["Font"]))
	$DocBuilder->Output = str_replace(
	    "DocumentBuilderFont",
	    "url('data:font/woff2;base64,".base64_encode(file_get_contents($DocBuilder->Configuration["Font"]))."') format('woff2')",
	    $DocBuilder->Output
	);
    else
	$DocBuilder->Output = str_replace("DocumentBuilderFont", "Arial", $DocBuilder->Output);
}
