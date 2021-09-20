<?php

function AddInstruction($ex, $num)
{
    extract($GLOBALS);

    // Pour faciliter la lecture du code généré
    $Name = Translate($ex["Name"]);
    Separator("Insertion de $Name");
    Title($Name, $num);

    // La documentation de l'exercice est faite dans la configuration directement
    if (isset($ex["Document"]) && is_array($ex["Document"]))
    {

    }
    // C'est un fichier exterieur.
    else
    {
	$ext = pathinfo($ex["Document"], PATHINFO_EXTENSION);
	if ($DocBuilder->Code == "html")
	{
	    // On a un fichier SGML, on l'intègre
	    if ($ext == "htm" || $ext == "php" || $ext == "phtml" || $ext == "html")
	    {
		if (!include($ex["Document"]))
		{
		    $DocBuilder->Errors[] = "Cannot include file {$ex["Document"]}.";
		    return ;
		}
	    }
	    // Sinon, on le lit comme si c'était un markdown
	    else
	    {
		echo ReadMarkdown($ex["Document"]);
	    }
	}
	else if ($DocBuilder->Code == "latex")
	{
	    // On a un fichier latex, on l'intègre
	    if ($ext == "tex")
	    {
		if (!include($ex["Document"]))
		{
		    $DocBuilder->Errors[] = "Cannot include file {$ex["Document"]}.";
		    return ;
		}
	    }
	    // Sinon, on le lit comme si c'était un markdown
	    else
	    {
		echo ReadMarkdown($ex["Document"]);
	    }
	}
    }
}

