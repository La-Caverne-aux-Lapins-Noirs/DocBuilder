<?php

function CompleteStyle(&$css)
{
    global $DocBuilder;
    $regex = "/[-+]?[0-9]*\.?[0-9]*cm/";

    $glob = false;
    // On parcoure les fichiers
    $new = [];
    foreach ($css->parsed as $top => &$cnt)
    {
	// On parcoure les declarations
	foreach ($cnt as $ss => &$r)
	{
	    $decl = explode(",", $ss);
	    foreach ($decl as $s)
	    {
		$s = trim($s);
		if ($s == ".page_content")
		{
		    foreach ($r as $prop => &$val)
		    {
			if ($prop == "height" && preg_match($regex, $val))
			    $DocBuilder->PageHeight = (float)$val;
			$new[$s][$prop] = $val;
		    }
		}
		else if ($s == "*" || $s == "p")
		{
		    foreach ($r as $prop => &$val)
		    {
			if ($prop == "line-height" && preg_match($regex, $val))
			    $DocBuilder->LineHeight = (float)$val;
			$new[$s][$prop] = $val;
		    }
		}
		else if (strlen($s) == 2 && substr($s, 0, 1) == "h")
		{
		    foreach ($r as $prop => &$val)
		    {
			if ($prop == "line-height" && preg_match($regex, $val))
			    $DocBuilder->TitleHeight[$s] = (float)$val;
			$new[$s][$prop] = $val;
		    }
		}
		else
		{
		    foreach ($r as $prop => &$val)
			$new[$s][$prop] = $val;
		}
	    }
	}
    }

    $final = "";
    foreach ($new as $decl => $map)
    {
	$final .= "$decl {\n";
	foreach ($map as $prop => $value)
	    $final .= "\t$prop:\t$value;\n";
	$final .= "}\n\n";
    }

    $DocBuilder->Style = $final;
    $css = new CssParser;
    $css->load_string($final);
    $css->parse();

    return ($css);
}

