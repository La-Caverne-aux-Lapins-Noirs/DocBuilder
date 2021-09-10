<?php

require_once (__DIR__."/src/Constants.php");
require_once (__DIR__."/deps/index.php");
require_once (__DIR__."/src/main.php");
exit(main(count($argv), $argv));
