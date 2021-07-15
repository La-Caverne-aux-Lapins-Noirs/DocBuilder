<?php

require_once ("./src/BuildPdf.php");

function BuildDocument()
{
    global $DocBuilder;

    if ($DocBuilder->Format == "PDF")
	BuildPdf();
}

