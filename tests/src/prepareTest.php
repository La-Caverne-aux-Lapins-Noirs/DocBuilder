<?php

require_once ("xtestcase.php");
require_once (__DIR__."/../../src/deps/index.php");
require_once (__DIR__."/../../src/src/LoadConfiguration.php");
require_once (__DIR__."/../../src/src/Prepare.php");

class prepareTest extends XTestCase
{
    public function testGetOption()
    {
	$opt["this"] = "Caverne aux lapins noirs";
	$opt["t"] = "Caverne aux lapins noirs";
	$this->assertSame(GetOption($opt, "this", "t", "LOL"), $opt["this"]);
	$this->assertSame(GetOption($opt, "woul", "t", "LOL"), $opt["t"]);
	$this->assertSame(GetOption($opt, "woul", "w", "LOL"), "LOL");
    }

    public function testLoadFile()
    {
	$opt["file"] = __DIR__."/../res/test.dab";
	$opt["f"] = __DIR__."/../res/test.dab";
	$out = "";
	$this->assertTrue(LoadFile($out, $opt, "file", "f", NULL, false));
	$this->assertSame($out, file_get_contents(__DIR__."/../res/test.dab"));

	$this->assertTrue(LoadFile($out, $opt, "woul", "f", NULL, true));
	$this->assertSame($out, LoadDabsic(__DIR__."/../res/test.dab"));

	$this->assertFalse(LoadFile($out, $opt, "woul", "w", NULL, true));
	$opt["file"] = "BANG";
	$this->assertFalse(LoadFile($out, $opt, "file", "w", NULL, true));
	$this->assertFalse(LoadFile($out, $opt, "file", "w", NULL, false));
    }
    public function testCompleteStyle()
    {
	global $DocBuilder;

	$css = new CssParser;
	$css->parsed = [
	    "file" => [
		"*" => [
		    "line-height" => "2cm"
		],
		"h1" => [
		    "line-height" => "1cm"
		],
		"h2" => [
		    "line-height" => "2cm"
		],
		"h3" => [
		    "line-height" => "3cm"
		],
		"h4" => [
		    "line-height" => "4cm"
		],
		"h5" => [
		    "line-height" => "5cm"
		],
	    ]
	];
	CompleteStyle($css);
	$this->assertTrue(abs($DocBuilder->LineHeight - 2) < 0.001);
	$this->assertTrue(abs($DocBuilder->TitleHeight["h1"] - 1) < 0.001);
	$this->assertTrue(abs($DocBuilder->TitleHeight["h2"] - 2) < 0.001);
	$this->assertTrue(abs($DocBuilder->TitleHeight["h3"] - 3) < 0.001);
	$this->assertTrue(abs($DocBuilder->TitleHeight["h4"] - 4) < 0.001);
	$this->assertTrue(abs($DocBuilder->TitleHeight["h5"] - 5) < 0.001);
    }
    public function testLoadConfiguration()
    {
	global $DocBuilder;
	global $Options;
	global $_SERVER;

	$options = [];
	ob_start();
	LoadConfiguration($options);
	$out = ob_get_contents();
	ob_end_clean();
	$this->assertTrue(strstr($out, "parameter: configuration") !== false);

    	$options = [
	    "c" => __DIR__."/../res/simple.dab"
	];
	ob_start();
	$this->assertSame(LoadConfiguration($options), false);
	$out = ob_get_contents();
	ob_end_clean();
	$this->assertTrue(strstr($out, "parameter: dictionnary") !== false);

	$options = [
	    "c" => __DIR__."/../res/simple.dab",
	    "d" => __DIR__."/../res/simple.dab",
	];
	ob_start();
	$this->assertSame(LoadConfiguration($options), false);
	$out = ob_get_contents();
	ob_end_clean();
	$this->assertTrue(strstr($out, "parameter: activity") !== false);

	$options = [
	    "c" => __DIR__."/../res/simple.dab",
	    "d" => __DIR__."/../res/simple.dab",
	    "a" => __DIR__."/../res/simple.dab",
	];
	ob_start();
	$this->assertSame(LoadConfiguration($options), false);
	$out = ob_get_contents();
	ob_end_clean();
	$this->assertTrue(strstr($out, "parameter: instance") !== false);

	$options = [
	    "c" => __DIR__."/../res/simple.dab",
	    "d" => __DIR__."/../res/simple.dab",
	    "a" => __DIR__."/../res/simple.dab",
	    "i" => __DIR__."/../res/simple.dab",
	];
	ob_start();
	$this->assertTrue(LoadConfiguration($options));
	$out = ob_get_contents();
	ob_end_clean();
	$this->assertSame($out, "");
    }
    public function testPrepare()
    {
	global $DocBuilder;
	global $Options;
	global $_SERVER;

	// Reset
	$DocBuilder = GetDocBuilder();
	$Options = GetOptions();
	$options = [
	    "c" => __DIR__."/../res/simple.dab",
	    "d" => __DIR__."/../res/simple.dab",
	    "a" => __DIR__."/../res/simple.dab",
	    "i" => __DIR__."/../res/simple.dab",
	    "m" => "/medals/",
	    "o" => "output.pdf",
	    "e" => "html",
	    "no-pretty" => true,
	    "keep-trace" => true,
	    "small-opening" => true,
	];
	LoadConfiguration($options);
	Prepare($options);
	$ref = LoadDabsic(__DIR__."/../res/simple.dab");

	$this->assertSame($DocBuilder->Configuration, $ref);
	$this->assertSame($DocBuilder->Dictionnary, $ref);
	$this->assertSame($DocBuilder->Activity, $ref);

	$ref["AcquiredMedals"] = ["pvc"];
	$ref["FullName"] = "Jason Brillante";
	$this->assertSame($DocBuilder->Instance, $ref);

	$this->assertSame($DocBuilder->MedalsDir, "/medals/");
	$this->assertTrue(strlen($DocBuilder->Style) > strlen(file_get_contents(__DIR__."/../res/style.css")));
	$this->assertSame($DocBuilder->OutputFile, "output.pdf");
	$this->assertSame($DocBuilder->Language, "FR");
	$this->assertSame($DocBuilder->Code, "html");
	$this->assertFalse($DocBuilder->Pretty);
	$this->assertTrue($DocBuilder->KeepTrace);
	$this->assertTrue($DocBuilder->SmallOpening);

	unset($DocBuilder->Instance["Language"]);
	$DocBuilder->Language = "";
	$this->assertTrue(Prepare($options));
	$this->assertSame($DocBuilder->Language, "FR");

	unset($DocBuilder->Activity["Language"]);
	$DocBuilder->Language = "";
	$DocBuilder->Configuration["Style"] =
	    "body {\n\tcolor: pink;\n}\n"
	    ;
	$this->assertTrue(Prepare($options));
	$this->assertSame($DocBuilder->Language, "FR");

	unset($DocBuilder->Configuration["Language"]);
	$DocBuilder->Language = "";
	unset($DocBuilder->Instance["AcquiredMedals"]);
	$options["l"] = "FR";
	$options["f"] = "PDFA5";
	$options["s"] = __DIR__."/../res/style.css";
	$this->assertTrue(Prepare($options));
	$this->assertSame($DocBuilder->Language, "FR");

	$DocBuilder->Language = "";
	unset($options["l"]);
	unset($DocBuilder->Configuration["Format"]);
	$this->assertTrue(Prepare($options));
	$this->assertSame($DocBuilder->Language, "FR");

	$DocBuilder->Configuration["Style"] = "/notexist";
	ob_start();
	$this->assertFalse(Prepare($options));
	$out = ob_get_contents();
	ob_end_clean();
	$this->assertTrue(strstr($out, "Specified file /notexist") !== false);
    }
}
