<?php

function PostProcess()
{
    extract($GLOBALS);

    foreach ($DocBuilder->ExercicePage as $name => $page)
    {
	$DocBuilder->Output = str_replace("!@@@$name@@@!", $page, $DocBuilder->Output);
    }

    $DocBuilder->Output = str_replace("@PAGECOUNT@", "$PageCount", $DocBuilder->Output);

    // Parceque le parseur de CSS n'est pas un parseur LL mais utile explode de manière naive.
    $DocBuilder->Output = str_replace(
	"url(data:font/woff2;base64,DocumentBuilderFont)",
	"",
	$DocBuilder->Output
    );
    
    if (strlen(@$DocBuilder->Configuration["Font"]))
	$DocBuilder->Output = str_replace(
	    "DocumentBuilderFont",
	    "url('data:font/woff2;base64,".base64_encode(file_get_contents($DocBuilder->Configuration["Font"]))."') format('woff2')",
	    $DocBuilder->Output
	);
    else
	$DocBuilder->Output = str_replace("DocumentBuilderFont", "Arial", $DocBuilder->Output);
}

