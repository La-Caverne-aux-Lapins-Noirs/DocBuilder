<?php

function FakeBracket($str, $bk)
{
    $str = str_replace("@".$bk."[", "<div class='".strtolower($bk)."'>", $str);
    $str = str_replace("@$bk]", "</div>", $str);
    return ($str);
}

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

    foreach (["ALERT", "HINT", "SHELL", "WARNING"] as $fb)
	$DocBuilder->Output = FakeBracket($DocBuilder->Output, $fb);

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

    // Les blocs à prettifier
    $Doc = explode("@CODE", $DocBuilder->Output);
    $Generated = [];
    for ($i = 0; $i < count($Doc); ++$i)
    {
	if (substr($Doc[$i], 0, 1) == "[")
	{
	    $Doc[$i] = substr($Doc[$i], 1);
	    if (substr($Doc[$i], 0, 3) == "(C)")
	    {
		$Doc[$i] = substr($Doc[$i], 3);
		$Doc[$i] = CLanguage($Doc[$i]);
	    }
	    $Generated[] = "<div class='code'>".$Doc[$i];
	}
	else if (substr($Doc[$i], 0, 1) == "]")
	    $Generated[] = "</div>".substr($Doc[$i], 1);
	else
	    $Generated[] = $Doc[$i];
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
    if (strlen(@$DocBuilder->Configuration["Font"]) && !NO_BASE64)
	$DocBuilder->Output = str_replace(
	    "DocumentBuilderFont",
	    "url('data:font/woff2;base64,".base64_encode(file_get_contents($DocBuilder->Configuration["Font"]))."') format('woff2')",
	    $DocBuilder->Output
	);
    else
	$DocBuilder->Output = str_replace("DocumentBuilderFont", "Arial", $DocBuilder->Output);

    $DocBuilder->Output = str_replace(
	"url(data:font/woff2;base64,ConsoleFont)",
	"",
	$DocBuilder->Output
    );
    if (strlen(@$DocBuilder->Configuration["ConsoleFont"]) && !NO_BASE64)
	$DocBuilder->Output = str_replace(
	    "ConsoleFont",
	    "url('data:font/woff2;base64,".base64_encode(file_get_contents($DocBuilder->Configuration["ConsoleFont"]))."') format('woff2')",
	    $DocBuilder->Output
	);
    else
	$DocBuilder->Output = str_replace("ConsoleFont", "Arial", $DocBuilder->Output);

    $DocBuilder->Output = str_replace("@@HLINE", "<hr />", $DocBuilder->Output);

    $fnds = [];
    if (preg_match_all('/\[imgd[ ]*([ a-zA-Z0-9_\.\:\"\'\/\\;]*)\][ ]*([a-zA-Z0-9_\.\/\\\\]+)[ ]*\[\/img\]/', $DocBuilder->Output, $fnds, PREG_SET_ORDER) !== false)
    {
	foreach ($fnds as $fnd)
	{
	    $DocBuilder->Output = str_replace(
		$fnd[0],
		"<div class='docdivpicture' style='background-image: url(".Base64Picture(trim($fnd[2]))."); ".trim($fnd[1]).";'></div>",
		$DocBuilder->Output
	    );
	}
    }

    $fnds = [];
    if (preg_match_all('/\[img[ ]*([ a-zA-Z0-9_\.\:\"\'\/\\;]*)\][ ]*([a-zA-Z0-9_\.\/\\\\]+)[ ]*\[\/img\]/', $DocBuilder->Output, $fnds, PREG_SET_ORDER) !== false)
    {
	foreach ($fnds as $fnd)
	{
	    $DocBuilder->Output = str_replace(
		$fnd[0],
		"<img class='docpicture' src='".Base64Picture(trim($fnd[2]))."' style='".trim($fnd[1]).";' />",
		$DocBuilder->Output
	    );
	}
    }
}
