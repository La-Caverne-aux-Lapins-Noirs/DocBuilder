<?php

require_once ("xtestcase.php");

class buildTest extends XTestCase
{
    public function testBuildEntry()
    {
	global $DocBuilder;

	$DocBuilder->Format = "PDFA4";
	$DocBuilder->Code = "html";
	$DocBuilder->Language = "FR";
	$DocBuilder->Dictionnary["FR"]["Name"] = "NOM";
	$DocBuilder->Dictionnary["FR"]["FilesToDeliver"] = "AA";
	$DocBuilder->Dictionnary["FR"]["SourceDirectories"] = "BB";
	$DocBuilder->Dictionnary["FR"]["IncludeDirectories"] = "CC";
	$DocBuilder->Dictionnary["FR"]["CompileFlags"] = "DD";
	

	$ex = [
	    "Name" => "Name",
	    "Document" => [
		"FilesToDeliver" => "A.c",
		"SourceDirectories" => ["B/", "BB/"],
		"IncludeDirectories" => "C/",
		"CompileFlags" => ["-D"]
	    ],
	];
	$num = [5, 2];

	ob_start();
	BuildEntry($ex, $num);
	$out = ob_get_contents();
	ob_end_clean();
	$ref =
	    "<p style=\"left-margin: 3cm;\">".
	    "V 2 - NOM".
	    "</p>".
	    "<b>AA</b>: A.c<br />".
	    "<b>BB</b>: B/, BB/<br />".
	    "<b>CC</b>: C/<br />".
	    "<b>DD</b>: -D<br />".
	    ""
	    ;
	$this->assertSame($ref, $out);
    }
}
