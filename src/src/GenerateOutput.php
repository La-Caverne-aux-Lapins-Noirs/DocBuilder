<?php

function GenerateOutput()
{
    global $DocBuilder;

    // Si des erreurs critiques ont été rencontrées, on les décrit puis on ajoute
    // les mises en gardes.
    if (count($DocBuilder->Debugs))
    {
	fprintf(STDERR, "Debug(s) about scripts:\n");
	foreach ($DocBuilder->Debugs as $w)
            fprintf(STDERR, "- $w.\n");
    }
    if (count($DocBuilder->Errors))
    {
	fprintf(STDERR, "GENERATION FAILURE:\nError(s) found in scripts:\n");
	foreach ($DocBuilder->Errors as $e)
	    fprintf(STDERR, "- $e.\n");
	fprintf(STDERR, "Warning(s) about scripts:\n");
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

	$ext = pathinfo($DocBuilder->OutputFile, PATHINFO_EXTENSION);
	
	if (strtolower($ext) == "pdf")
	{
	    $tmp = $DocBuilder->OutputFile.".htm";
	    system("rm -f $tmp");
	    file_put_contents($tmp, $DocBuilder->Output);
	    $out = [];
	    $result = 0;
	    exec(
		"chromium ".
		"--headless ".
		"--disable-gpu ".
		"--run-all-compositor-stages-before-draw ".
		"--print-to-pdf-no-header ".
		"--print-to-pdf='".$DocBuilder->OutputFile."~' ".
		"file://".getcwd()."/".$tmp,
		$out, $result
	    );
	    if ($DocBuilder->KeepTrace == false)
		shell_exec("rm -f $tmp");
	    RemoveLastPage($DocBuilder->OutputFile);
	    if ($result != 0)
	    {
		fprintf(STDERR, "PDF Generation failed: %s\n", implode("\n", $out));
		exit(1);
	    }
	}
	else
	    file_put_contents($DocBuilder->OutputFile, $DocBuilder->Output);
    }
}
