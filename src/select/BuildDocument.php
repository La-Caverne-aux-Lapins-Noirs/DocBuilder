<?php // @codeCoverageIgnoreStart

function BuildDocument()
{
    extract($GLOBALS);

    if (strncmp($DocBuilder->Format, "PDF", 3) == 0)
	BuildPdf();
    else
	BuildWeb();
}

// @codeCoverageIgnoreEnd
