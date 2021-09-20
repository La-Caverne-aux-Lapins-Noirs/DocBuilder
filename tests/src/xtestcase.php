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

function LogText($txt = NULL)
{
    if (0) // Une fois qu'on a plus beosin de debugger...
	return ;

    $logfile = __DIR__."/../log";
    if ($txt == NULL)
	file_put_contents($logfile, "");
    else
    {
	$log = file_get_contents($logfile);
	file_put_contents($logfile, $log.$txt."\n");
    }
}

function LogTextR($obj)
{
    LogText(print_r($obj, true));
}

LogText();

class XTestCase extends TestCase
{
    public $user = NULL;

    public function all()
    {

    }
}

// @codeCoverageIgnoreStop
