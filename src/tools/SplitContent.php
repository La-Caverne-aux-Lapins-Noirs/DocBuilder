<?php

function SplitContent($text)
{
    global $DocBuilder;

    // On recupere le texte du chapitre et effectuons un découpage du contenu.
    // Tout ce qui peut imposer un espacement vertical est compté.
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
    return ($NewOut);
}
