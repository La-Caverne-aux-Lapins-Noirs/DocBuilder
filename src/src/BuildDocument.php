<?php

require_once (__DIR__."/BuildPdf.php");

function BuildDocument()
{
    global $DocBuilder;

    if ($DocBuilder->Format == "PDF")
	BuildPdf();
}

