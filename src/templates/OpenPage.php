<?php

function OpenPage()
{
    global $DocBuilder;
    global $PageCount;
    global $Ex;

    if (file_exists($file = __DIR__."/../templates/".strtolower($DocBuilder->Format)."/top.php"))
	require ($file);
}
