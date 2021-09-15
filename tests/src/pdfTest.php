<?php

require_once ("xtestcase.php");
// require_once ("../pages/activities/manage_activity.php");

class pdfTest extends XTestCase
{
    public function testBuildPdfExercise()
    {
	$this->assertSame(BuildPdfExercise("Idle", 1), true);
	$this->assertSame(BuildPdfExercise("OpenPage", 1), true);
	$this->assertSame(BuildPdfExercise("ClosePage", 1), true);

    }
}

