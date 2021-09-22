<?php

function GenerateOutput()
{
    global $DocBuilder;

    // Si des erreurs critiques ont été rencontrées, on les décrit puis on ajoute
    // les mises en gardes.
    if (count($DocBuilder->Errors))
    {
	fprintf(STDERR, "GENERATION FAILURE:\nError(s) found in scripts:\n");
	foreach ($DocBuilder->Errors as $e)
	    fprintf(STDERR, "- $e.\n");
	fprintf(STDERR, "Warnings about scripts:\n");
	foreach ($DocBuilder->Warnings as $w)
            fprintf(STDERR, "- $w.\n");
	exit(1);
    }
    // Sinon, on écrit les mises en garde à la suite du document généré
    else
    {
	foreach ($DocBuilder->Warnings as $w)
	{
	    if ($DocBuilder->Code == "html")
		$DocBuilder->Output .= "\n<!-- WARNING: $w -->";
	    else if ($DocBuilder->Code == "latex")
		$DocBuilder->Output .= "\n% WARNING: $w";
	    else
		$DocBuilder->Output .= "\nWARNING: $w";
	}
	$DocBuilder->Output .= "\n";
	// Indent
	if ($DocBuilder->Pretty)
	    $DocBuilder->Output = Prettyficator($DocBuilder->Output);
	// Encode special characters
	$DocBuilder->Output = LanguageEncoder($DocBuilder->Output);
	
	if (strtolower(substr($DocBuilder->Format, 0, 3)) == "pdf")
	{
	    $tmp = $DocBuilder->OutputFile.".htm";
	    file_put_contents($tmp, $DocBuilder->Output);
	    $out = shell_exec(
		"chromium-browser --headless --disable-gpu ".
		"--print-to-pdf='".$DocBuilder->OutputFile."' ".
		"file://".getcwd()."/".$tmp
	    );
	    if ($DocBuilder->KeepTrace == false)
		shell_exec("rm -f $tmp");
	    if ($out != "")
	    {
		fprintf(STDERR, "%s\n", $out);
		exit(1);
	    }
	}
	else
	    file_put_contents($DocBuilder->OutputFile, $DocBuilder->Output);
    }
}
