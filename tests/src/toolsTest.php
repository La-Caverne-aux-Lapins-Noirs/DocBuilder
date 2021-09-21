<?php

require_once ("xtestcase.php");

function Accumulate($ex, $depth) // Pour tester BrowseExercises
{
    global $AccumulationTest;

    $str = "";
    foreach ($depth as $d)
    {
	$str .= $d;
    }
    $AccumulationTest[] = $ex.$str;
}

class toolsTest extends XTestCase
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
    public function testTranslate()
    {
	global $DocBuilder;

	$DocBuilder->Language = "FR";
	$this->assertSame(Translate(["FR" => "DEF", "EN" => "def"]), "DEF");
	$DocBuilder->Language = "EN";
	$this->assertSame(Translate(["FR" => "DEF", "EN" => "def"]), "def");
	$DocBuilder->Language = "ZULU";
	$DocBuilder->Warnings = [];
	$this->assertSame(Translate(["FR" => "DEF", "EN" => "def"]), "DEF");
	$this->assertSame(count($DocBuilder->Warnings), 1);

	$this->assertSame(Translate("!#"), "!#");

	$DocBuilder->Dictionnary["FR"]["xyz"] = "XYZ";
	$DocBuilder->Dictionnary["EN"]["xyz"] = "123";
	$DocBuilder->Language = "FR";
	$this->assertSame(Translate("xyz"), "XYZ");
	$DocBuilder->Language = "EN";
	$this->assertSame(Translate("xyz"), "123");
	$DocBuilder->Language = "ZULU";
	$this->assertSame(Translate("xyz"), "XYZ");
	$this->assertSame(count($DocBuilder->Warnings), 2);

	$this->assertSame(Translate("Moulaga"), "Moulaga");
    }
    public function testMustPrint()
    {
	global $DocBuilder;

	$DocBuilder->Errors = [];
	$param = ["abc" => "def"];

	$this->assertSame(MustPrint($param, "abc", false), "def");

	$this->assertSame(MustPrint($param, "abcd", false, "XXX"), "");
	$this->assertSame(count($DocBuilder->Errors), 1);
	$this->assertSame(substr($DocBuilder->Errors[0], 0, 4), "XXX:");
	$this->assertSame($param["abcd"], "");

	$DocBuilder->Language = "FR";
	$DocBuilder->Dictionnary["FR"]["doki"] = "DOKI";
	$param = ["oki" => "doki"];
	$this->assertSame(MustPrint($param, "oki", true), "DOKI");
    }
    public function testTryPrint()
    {
	global $DocBuilder;

	$param = ["abc" => "def"];
	$DocBuilder->Warnings = [];

	$this->assertSame(TryPrint($param, "abc", false), "def");

	$this->assertSame(TryPrint($param, "abcd", false, "XXX"), "");
	$this->assertSame(count($DocBuilder->Warnings), 1);
	$this->assertSame(substr($DocBuilder->Warnings[0], 0, 4), "XXX:");
	$this->assertSame($param["abcd"], "");

	$DocBuilder->Language = "FR";
	$DocBuilder->Dictionnary["FR"]["doki"] = "DOKI";
	$param = ["oki" => "doki"];
	$this->assertSame(TryPrint($param, "oki", true), "DOKI");
    }
    public function testPrintMeta()
    {
	global $DocBuilder;

	$param = ["abc" => "ABC"];
	$DocBuilder->Language = "FR";
	$DocBuilder->Dictionnary["FR"]["ABC"] = "CONTENT";

	ob_start();
	PrintMeta($param, "This is abc", "abc", false);
	$out = ob_get_contents();
	ob_end_clean();
	$this->assertSame($out, "<meta name='This is abc' content='CONTENT' />\n");

	ob_start();
	PrintMeta($param, "This is abc", "abc", true);
	$out = ob_get_contents();
	ob_end_clean();
	$this->assertSame($out, "<meta name='This is abc' content='CONTENT' />\n");

	ob_start();
	PrintMeta($param, "This is abc", "empty");
	$out = ob_get_contents();
	ob_end_clean();
	$this->assertSame($out, "");
    }
    public function testPrintMarkup()
    {
	global $DocBuilder;

	$param = ["abc" => "ABC"];
	$DocBuilder->Language = "FR";
	$DocBuilder->Dictionnary["FR"]["ABC"] = "CONTENT";

	$html = "<div>@@</div>";

	ob_start();
	PrintMarkup($param, "abc", $html, true, false);
	$out = ob_get_contents();
	ob_end_clean();
	$this->assertSame($out, "<div>CONTENT</div>\n");

	ob_start();
	PrintMarkup($param, "abc", $html, false, true);
	$out = ob_get_contents();
	ob_end_clean();
	$this->assertSame($out, "<div>ABC</div>\n");

	ob_start();
	PrintMarkup($param, "empty", $html);
	$out = ob_get_contents();
	ob_end_clean();
	$this->assertSame($out, "");
    }

    public function testPrintImage()
    {
	global $DocBuilder;

	$param = ["abc" => "ABC", "Picture" => "picture.jpg"];
	$DocBuilder->Language = "FR";
	$DocBuilder->Dictionnary["FR"]["ABC"] = "CONTENT";

	$html = "<div>@@</div>";

	ob_start();
	PrintImage($param, "Picture", "abc", "<div>@@</div>", "Warp9", false, "XXX");
	$out = ob_get_contents();
	ob_end_clean();
	$this->assertSame($out, "<img src='picture.jpg' alt='CONTENT' Warp9 />\n");

	ob_start();
	PrintImage($param, "Pikchoure", "abc", "<div>@@</div>", "Warp9", true, "XXX");
	$out = ob_get_contents();
	ob_end_clean();
	$this->assertSame($out, "<div>CONTENT</div>\n");

	ob_start();
	PrintImage($param, "empty", $html);
	$out = ob_get_contents();
	ob_end_clean();
	$this->assertSame($out, "");
    }
    public function testRetrieveMedal()
    {
	global $DocBuilder;

	$DocBuilder->Language = "FR";
	$DocBuilder->MedalsDir = __DIR__."/../res/medal";
	$DocBuilder->Dictionnary["FR"]["FunctionSDone"] = "Fonction %s réussite";

	shell_exec("rm -f ".__DIR__."/../res/medal/basic.png");
	$medal = RetrieveMedal("basic");
	$this->assertSame($medal["CodeName"], "basic");
	$this->assertSame($medal["Name"], "Basique");
	$this->assertSame($medal["Description"], "Médaille simple");
	$this->assertSame($medal["Icon"], __DIR__."/../res/medal/basic.png");
	$this->assertSame($medal["Type"], "coin");

	shell_exec("rm -f ".__DIR__."/../res/medal/other.dab");
	shell_exec("rm -f ".__DIR__."/../res/medal/other.png");
	$medal = RetrieveMedal("other");
	$this->assertSame($medal["CodeName"], "other");
	$this->assertSame($medal["Name"], "other");
	$this->assertSame($medal["Description"], "Fonction other réussite");
	$this->assertSame($medal["Icon"], __DIR__."/../res/medal/other.png");
	$this->assertSame($medal["Type"], "band");
	$this->assertTrue(file_exists($medal["Icon"]));

	$medal = RetrieveMedal("other"); // Pour la partie récupération du type
	$this->assertSame($medal["CodeName"], "other");
    }
    public function testGetLocalMedals()
    {
	$act = [
	    "Exercices" => [
		// Un exercice directement present
		[
		    "Medals" => ["basic", "other"]
		],
		// Deux sous exercices
		[
		    [
			"Medals" => "red"
		    ],
		    [
			"Medals" => ["simple", "advanced", "basic"]
		    ]
		]
	    ]
	];
	$meds = GetLocalMedals($act);
	$this->assertSame($meds, [
	    "basic" => 2,
	    "other" => 1,
	    "red" => 1,
	    "simple" => 1,
	    "advanced" => 1
	]);
    }
    public function testGetGlobalMedals()
    {
	global $DocBuilder;

	$DocBuilder->Activity["Exercises"] = [
	    [
		"Medals" => ["red", "green", "blue", "pink"]
	    ],
	    [
		"Medals" => "teal"
	    ]
	];
	$DocBuilder->Instance["AcquiredMedals"] = [
	    "green", "blue", "teal"
	];

	$meds = GetGlobalMedals();

	foreach ($meds as $i => $m)
	{
	    $this->assertSame($i, $m["CodeName"]);
	    $this->assertSame($DocBuilder->MedalsDir."/".$i.".png", $m["Icon"]);
	    $this->assertTrue(file_exists($m["Icon"]));
	}
    }
    public function testGlobals()
    {
	require (__DIR__."/../../src/src/Globals.php");
	$this->assertNotNull($DocBuilder);
	$this->assertNotNull($Markdown);
	$this->assertNotNull($Options);
	$this->assertsame($PageCount, 1);
	$this->assertsame($ChapterCount, 0);
	$this->assertsame($SubOutput, "");
	$this->assertsame($NewPage, false);
    }
    public function testKeepContent()
    {
	global $DocBuilder;

	$DocBuilder->Output = "";
	$this->assertSame(KeepContent("abc"), "");
	$this->assertSame($DocBuilder->Output, "abc");
	$this->assertSame(KeepContent("def"), "");
	$this->assertSame($DocBuilder->Output, "abcdef");
    }
    public function testSplitContent()
    {
	global $DocBuilder;

	$text = "<p>A<br />B</p><h1>C</h1>";
	$text = SplitContent($text);
	$this->assertSame($text, ["<p>A<br />", "B</p>", "<h1>C</h1>"]);
    }
    public function testPaginize()
    {
	global $DocBuilder;
	global $Ex;

	$DocBuilder->Format = "PDFA4";

	$DocBuilder->Code = "latex";
	ob_start();
	Paginize("A@PAGEBREAKB");
	$out = ob_get_contents();
	ob_end_clean();
	$this->assertSame($out, "A\\newpageB");

	$DocBuilder->Code = "rubber";
	ob_start();
	Paginize("A@PAGEBREAKB");
	$out = ob_get_contents();
	ob_end_clean();
	$this->assertSame($out, "");

	$DocBuilder->Code = "html";
	ob_start();
	Paginize("<p>A<br />B</p><h1>C</h1>");
	$out = ob_get_contents();
	ob_end_clean();
	$out = explode("\n", $out);
	foreach ($out as &$l)
	    $l = trim($l);
	$out = implode("\n", $out);
	$this->assertSame($out,
			  "<div class=\"page\">\n".
			  "<table class=\"page_header\">\n".
			  "<tr><td class=\"left_align page_matter_logo\">\n".
			  "</td><td class=\"right_align page_activity_name\">\n".
			  "</td></tr>\n".
			  "</table>\n".
			  "<div class=\"page_content\">\n".
			  "<p>A<br />B</p><h1>C</h1>    </div>\n".
			  "<table class=\"page_footer\">\n".
			  "<tr><td class=\"page_activity_logo\">\n".
			  "</td><td class=\"page_counter\">\n".
			  "1 / @PAGECOUNT@\n".
			  "</td><td class=\"page_school_logo\">\n".
			  "</td></tr>\n".
			  "</table>\n".
			  "</div>\n"
	);

	// On verifie la pagination.
	// 18cm de haut, 11cm de large
	// Dont 16cm de haut pour le contenu
	$DocBuilder->Format = "PDFA5";
	$DocBuilder->PageHeight = 16;
	$DocBuilder->LineHeight = 0.3; // 53 lignes
	$DocBuilder->TitleHeight["h1"] = 1.0;
	$DocBuilder->TitleHeight["h2"] = 1.0;
	$DocBuilder->TitleHeight["h3"] = 1.0;
	$DocBuilder->TitleHeight["h4"] = 1.0;
	$DocBuilder->TitleHeight["h5"] = 1.0;
	ob_start();
	Paginize(file_get_contents(__DIR__."/../res/triple_page.htm"));
	$out = ob_get_contents();
	ob_end_clean();
	$out = explode("\n", $out);
	foreach ($out as &$l)
	    $l = trim($l);
	$out = implode("\n", $out);
	$this->assertSame($out, file_get_contents(__DIR__."/../res/triple_page_generated.htm"));

	// Pour la marque de coté
	$Ex["SideMark"] = "!!!";
	ob_start();
	Paginize(file_get_contents(__DIR__."/../res/sidemark.htm"));
	$out = ob_get_contents();
	ob_end_clean();
	$out = explode("\n", $out);
	foreach ($out as &$l)
	    $l = trim($l);
	$out = implode("\n", $out);
	$sidemark =
	    "<div class=\"page\">\n".
	    "<table class=\"page_header\">\n".
	    "<tr><td class=\"center_align page_activity_name\">\n".
	    "</td></tr>\n".
	    "</table>\n".
	    "<div class=\"page_sidemark\">\n".
	    "!!!	</div>\n".
	    "<div class=\"page_content page_shift\">\n".
	    "ABC    </div>\n".
	    "<table class=\"page_footer\">\n".
	    "<tr><td class=\"page_counter\">\n".
	    "1	</td></tr>\n".
	    "</table>\n".
	    "</div>\n"
	    ;
	$this->assertSame($out, $sidemark);

	ob_start();
	$DocBuilder->Format = "PDFA4";
	Paginize(file_get_contents(__DIR__."/../res/sidemark.htm"));
	$out = ob_get_contents();
	ob_end_clean();
	$out = explode("\n", $out);
	foreach ($out as &$l)
	    $l = trim($l);
	$out = implode("\n", $out);
	$sidemark =
	    "<div class=\"page\">\n".
	    "<table class=\"page_header\">\n".
	    "<tr><td class=\"left_align page_matter_logo\">\n".
	    "</td><td class=\"right_align page_activity_name\">\n".
	    "</td></tr>\n".
	    "</table>\n".
	    "<div class=\"page_sidemark\">\n".
	    "!!!	</div>\n".
	    "<div class=\"page_content page_shift\">\n".
	    "ABC    </div>\n".
	    "<table class=\"page_footer\">\n".
	    "<tr><td class=\"page_activity_logo\">\n".
	    "</td><td class=\"page_counter\">\n".
	    "1 / @PAGECOUNT@\n".
	    "</td><td class=\"page_school_logo\">\n".
	    "</td></tr>\n".
	    "</table>\n".
	    "</div>\n"
	    ;
	$this->assertSame($out, $sidemark);
    }
    public function testSubKeepContent()
    {
	global $SubOutput;

	$SubOutput = "";
	$this->assertSame(SubKeepContent("a"), "");
	$this->assertSame($SubOutput, "a");
	$this->assertSame(SubKeepContent("b"), "");
	$this->assertSame($SubOutput, "ab");
    }
    public function testToRoman()
    {
	$this->assertSame(ToRoman(3), "III");
	$this->assertSame(ToRoman(4), "IV");
	$this->assertSame(ToRoman(5), "V");
	$this->assertSame(ToRoman(8), "VIII");
	$this->assertSame(ToRoman(9), "IX");
	$this->assertSame(ToRoman(10), "X");
	$this->assertSame(ToRoman(14), "XIV");
	$this->assertSame(ToRoman(42), "XLII");
    }
    public function testMergeNumber()
    {
	$this->assertSame(MergeNumber([10]), "X");
	$this->assertSame(MergeNumber([10, 2]), "X 2");
	$this->assertSame(MergeNumber([10, 2, 4]), "X 2.4");
	$this->assertSame(MergeNumber([10, 2, 4, 6]), "X 2.4.6");
    }
    public function testArrayPush()
    {
	$a = [];
	$b = ArrayPush($a, 1);
	$c = ArrayPush($b, 2);
	$d = ArrayPush($c, 3);
	$e = ArrayPush($d, 4);
	$this->assertSame($e, [1, 2, 3, 4]);
    }
    public function testBrowseExercices()
    {
	global $AccumulationTest;

	$array = [
	    "A",
	    [
		"B",
		"B"
	    ],
	    [
		[
		    "C",
		    "C"
		],
		[
		    "D",
		    ["NoDoc" => true],
		    "D",
		]
	    ],
	    [
		[
		    "E",
		    "E"
		],
		[
		    "F",
		    "F",
		]
	    ]
	];
	$ref = [
	    "A1",
	    "B21", "B22",
	    "C311", "C312",
	    "D321", "D322",
	    "E411", "E412",
	    "F421", "F422"
	];
	$depth = [];
	$AccumulationTest = [];
	BrowseExercises($array, $depth, "Accumulate");
	$this->assertSame($AccumulationTest, $ref);
    }
    public function testWriteLabel()
    {
	global $DocBuilder;

	$DocBuilder->Dictionnary["FR"] = ["A" => "C"];
	$DocBuilder->Language = "FR";
	ob_start();
	WriteLabel(["A" => ["B", "D"]], "A");
	$out = ob_get_contents();
	ob_end_clean();
	$this->assertSame($out, "<b>C</b>: B, D<br />");

	ob_start();
	WriteLabel(["A" => ["B", "D"]], "B");
	$out = ob_get_contents();
	ob_end_clean();
	$this->assertSame($out, "");
    }
    public function testWriteTable()
    {
	global $DocBuilder;

	ob_start();
	$DocBuilder->Dictionnary["FR"] = ["A" => "C", "B" => "D"];
	$DocBuilder->Language = "FR";
	WriteTable(["A" => ["1", "2"], "B" => ["3", "4"]], ["A", "B"]);
	$out = ob_get_contents();
	ob_end_clean();
	$this->assertSame($out,
			  "<table><tr><th>C</th><th>D</th>".
			  "</tr><tr><td>1<br />2</td><td>3<br />4</td></tr></table>"
	);

	ob_start();
	WriteTable([], []);
	$out = ob_get_contents();
	ob_end_clean();
	$this->assertSame($out, "");

	ob_start();
	WriteTable(["C" => ["1", "2"]], ["A", "B"]);
	$out = ob_get_contents();
	ob_end_clean();
	$this->assertSame($out, "");
    }
    public function testPrintTitle()
    {
	$Surface =
	    "<h3 style=\"left-margin: 1cm;\">".
	    "I 1 - Name".
	    "</h3>"
	    ;
	ob_start();
	PrintTitle(["Name" => "Name"], [1, 1]);
	$out = ob_get_contents();
	ob_end_clean();
	$this->assertSame($Surface, $out);

	$Deep =
	    "<p style=\"left-margin: 3cm;\">".
	    "I 1.1.1.1 - Name".
	    "</p>"
	    ;
	ob_start();
	PrintTitle(["Name" => "Name"], [1, 1, 1, 1, 1]);
	$out = ob_get_contents();
	ob_end_clean();
	$this->assertSame($Deep, $out);
    }
}
