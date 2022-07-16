<?php

function ClosePage()
{
    global $DocBuilder;
    global $PageCount;
    global $Page;
    global $Ex;

    if (file_exists($file = __DIR__."/../templates/".strtolower($DocBuilder->Pager)."/bottom.php"))
	require ($file);
}
