<?php

require_once ("xtestcase.php");
require_once (__DIR__."/../../src/deps/index.php");
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
	Prepare($options);
	$this->assertSame($DocBuilder->Configuration, LoadDabsic(__DIR__."/../res/simple.dab"));
	$this->assertSame($DocBuilder->Dictionnary, LoadDabsic(__DIR__."/../res/simple.dab"));
	$this->assertSame($DocBuilder->Activity, LoadDabsic(__DIR__."/../res/simple.dab"));
	$this->assertSame($DocBuilder->Instance, LoadDabsic(__DIR__."/../res/simple.dab"));
	$this->assertSame($DocBuilder->MedalsDir, "/medals/");
	$this->assertSame($DocBuilder->Style, file_get_contents(__DIR__."/../style_glue.css"));
	$this->assertSame($DocBuilder->OutputFile, "output.pdf");
	$this->assertSame($DocBuilder->Language, "FR");
	$this->assertSame($DocBuilder->Code, "html");
	$this->assertFalse($DocBuilder->Pretty);
	$this->assertTrue($DocBuilder->KeepTrace);
	$this->assertTrue($DocBuilder->SmallOpening);
    }
}

