<?php

function GetMarkdown()
{
    $Markdown = new Parsedown();
    $Markdown->setMarkupEscaped(true);
    $Markdown->setBreaksEnabled(true);
    // $Markdown->setUrlsLinked(false);
    return ($Markdown);
}

