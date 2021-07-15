<?php

function LanguageEncoder($str)
{
    $str = str_replace("é", "&eacute;", $str);
    $str = str_replace("è", "&egrave;", $str);
    $str = str_replace("ê", "&ecirc;", $str);
    $str = str_replace("à", "&agrave;", $str);
    $str = str_replace("â", "&acirc;", $str);
    //$str = utf8_encode($str);
    return ($str);
}

function Prettyficator($html)
{
    // return ($html);
    $Config = [
	"indent" => true,
	"output-xhtml" => false,
	"wrap" => 0,
	"char-encoding" => "utf8",
	"output-encoding" => "utf8",
    ];
    $Tidy = new tidy;
    $Tidy->parseString($html, $Config, "utf8");
    $Tidy->cleanRepair();
    return (tidy_get_output($Tidy)."\n");
}

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
            $DocBuilder->Output .= "\n<!-- WARNING: $w -->";
	$DocBuilder->Output .= "\n";
	// Indent
	if ($DocBuilder->Pretty)
	    $DocBuilder->Output = Prettyficator($DocBuilder->Output);
	// Encode special characters
	$DocBuilder->Output = LanguageEncoder($DocBuilder->Output);
	$ext = pathinfo($DocBuilder->OutputFile, PATHINFO_EXTENSION);
	if ($ext == "pdf")
	{
	    $tmp = $DocBuilder->OutputFile.".htm";
	    file_put_contents($tmp, $DocBuilder->Output);
	    $out = shell_exec("wkhtmltopdf --encoding utf-8 ".
			      "-B 0 -T 0 -L 0 -R 0 --page-width 15.75cm --page-height 23cm ".
			      "--title '".Translate($DocBuilder->Activity["Activity"])."' ".
			      "'$tmp' '$DocBuilder->OutputFile'"
	    );
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
