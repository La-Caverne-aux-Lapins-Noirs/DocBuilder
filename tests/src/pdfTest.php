<?php

require_once ("xtestcase.php");
// require_once ("../pages/activities/manage_activity.php");

class pdfTest extends XTestCase
{
    public function testAddInstruction()
    {
	
    }
    public function testBuildPdfExercise()
    {
	global $DocBuilder;

	$DocBuilder->Code = "html";
	$DocBuilder->Format = "PDFA5";
	ob_start("KeepContent");
	/*
	$this->assertSame(BuildPdfExercise("Idle", 1), true);
	$this->assertSame(BuildPdfExercise("OpenPage", 1), true);
	$this->assertSame(BuildPdfExercise("ClosePage", 1), true);
	 */
	$out = ob_get_contents();
	ob_end_clean();
	$this->assertSame($out, "");
    }
}

