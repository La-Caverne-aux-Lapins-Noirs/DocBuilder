<?php
// @codeCoverageIgnoreStart

use PHPUnit\Framework\TestCase;

@define("DOCBUILDER_DEFAULT_CONFIGURATION", NULL);
@define("DOCBUILDER_DEFAULT_DICTIONNARY", NULL);

require_once (__DIR__."/../../src/deps/index.php");
require_once (__DIR__."/../../src/tools/index.php");
require_once (__DIR__."/../../src/templates/index.php");
require_once (__DIR__."/../../src/src/index.php");

define("DEBUG", 1);

$DocBuilder = new CDocBuilder;
$Options = new Options;

LogText();

class XTestCase extends TestCase
{
    public $user = NULL;

    public function all()
    {

    }
}

// @codeCoverageIgnoreStop
