<?php

function OpenPage()
{
    global $DocBuilder;
    global $PageCount;
    global $Ex;
    global $Page;

    if (file_exists($file = __DIR__."/../templates/".strtolower($DocBuilder->Pager)."/top.php"))
	require ($file);
}
