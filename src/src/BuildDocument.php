<?php

require_once (__DIR__."/../pdf/BuildPdf.php");
require_once (__DIR__."/../web/BuildWeb.php");

function BuildDocument()
{
    extract($GLOBALS);

    if (strncmp($DocBuilder->Format, "PDF", 3) == 0)
	BuildPdf();
    else
	BuildWeb();
}

