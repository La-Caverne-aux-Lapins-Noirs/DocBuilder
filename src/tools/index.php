<?php // @codeCoverageIgnoreStart

$dir = opendir(__DIR__);
while (($file = readdir($dir)) !== false)
{
    if (is_dir($file))
	continue ;
    if ($file == basename(__FILE__, ".php"))
	continue;
    require_once ($file);
}
closedir($dir);
// @codeCoverageIgnoreEnd
