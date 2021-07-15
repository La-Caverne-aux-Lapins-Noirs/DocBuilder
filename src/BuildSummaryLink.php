<?php

function BuildSummaryLink()
{
    global $DocBuilder;
    global $PageCount;

    foreach ($DocBuilder->ExercicePage as $name => $page)
    {
	$DocBuilder->Output = str_replace("!@@@$name@@@!", $page, $DocBuilder->Output);
    }
    $DocBuilder->Output = str_replace("@PAGECOUNT@", "$PageCount", $DocBuilder->Output);
}

