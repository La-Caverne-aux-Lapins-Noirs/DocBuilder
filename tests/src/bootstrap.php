<?php
// @codeCoverageIgnoreStart
define("UNIT_TEST", "1");

function vline($v = "-")
{
    for ($i = 0; $i < 42; ++$i)
	echo $v;
    echo "\n";
}

$_SERVER["REMOTE_ADDR"] = "0.0.0.0";
$_SERVER["REQUEST_URI"] = "localhost";
// @codeCoverageIgnoreStop
