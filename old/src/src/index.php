<?php // @codeCoverageIgnoreStart

require_once (__DIR__."/CDocBuilder.php");
require_once (__DIR__."/GetMarkdown.php");
require_once (__DIR__."/Options.php");

$dir = opendir(__DIR__);
while (($file = readdir($dir)) !== false)
{
    if (is_dir($file))
	continue ;
    if ($file == basename(__FILE__, ".php"))
	continue;
    if (substr($file, -4, 4) != ".php")
	continue ;
    require_once ($file);
}
closedir($dir);

// @codeCoverageIgnoreEnd
