<?php

require_once ("xtestcase.php");
require_once (__DIR__."/../../src/tools/index.php");

class utilsTest extends XTestCase
{
    public function testIsArray()
    {
	$this->assertFalse(IsArray(42));
	$this->assertFalse(IsArray([]));
	$this->assertFalse(IsArray(["lol" => "lel", "lel" => "lol"]));
	$this->assertFalse(IsArray([1 => "lel", 3 => "lol"]));
	$this->assertTrue(IsArray([0 => "lel", 1 => "lol"]));
	$this->assertTrue(IsArray(["lel", "lol"]));
    }
    public function testMustBeAnArray()
    {
	$lel = NULL;
	$this->assertSame(MustBeAnArray($lel), []);
	$lel = [1, 2, 3];
	$this->assertSame(MustBeAnArray($lel), $lel);
	$lel = 3;
	$this->assertSame(MustBeAnArray($lel), [3]);
	$this->assertSame(MustBeAnArray($lel), $lel);
    }
    public function testSeparator()
    {
	global $DocBuilder;

	$DocBuilder->Code = "html";
	ob_start();
	Separator("A");
	$out = ob_get_contents();
	ob_end_clean();
	$this->assertSame("<!-- ============================================== -->\n".
			  "<!-- A -->\n", $out);
	$DocBuilder->Code = "latex";
	ob_start();
	Separator("A");
	$out = ob_get_contents();
	ob_end_clean();
	$this->assertSame("% =================================================\n".
			  "% A\n", $out);
    }
    public function testUsage()
    {
	global $Options;

	$Options->ShortOptions = "a:b";
	$Options->LongOptions = ["activity:", "bureau"];
	$Options->Description = ["DESCRIPTION", "BUREAU"];
	ob_start();
	$this->assertSame(Usage(), 1);
	$out = ob_get_contents();
	ob_end_clean();
	$this->assertSame("Usage is:\n".
			  "\t-a/--activity                    DESCRIPTION.\n".
			  "\t-b/--bureau                      BUREAU.\n",
			  $out
	);
    }
    public function testLoadDabsic()
    {
	$this->assertSame(LoadDabsic(__DIR__."/../res/test.dab"), ["Node" => ["Field" => "Hello"]]);
	$this->assertSame(LoadDabsic("BANG"), NULL);
    }
    public function testMergeData()
    {
	$in = "lol";
	$this->assertSame(MergeData($in), "lol");
	$in = ["lol", "ol"];
	$this->assertSame(MergeData($in), "lolol");
    }
}

