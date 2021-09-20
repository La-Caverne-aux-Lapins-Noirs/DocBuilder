<?php

function ClosePage()
{
    global $DocBuilder;
    global $PageCount;
    global $Ex;

    if (file_exists($file = __DIR__."/../templates/".strtolower($DocBuilder->Format)."/bottom.php"))
	require ($file);
}
