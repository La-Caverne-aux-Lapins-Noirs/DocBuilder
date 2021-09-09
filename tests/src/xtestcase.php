<?php

use PHPUnit\Framework\TestCase;

require_once (__DIR__."/../../src/src/CDocBuilder.php");
require_once (__DIR__."/../../src/src/Options.php");

$DocBuilder = new CDocBuilder;
$Options = new Options;

class XTestCase extends TestCase
{
    public $user = NULL;

    public function all()
    {

    }
}
