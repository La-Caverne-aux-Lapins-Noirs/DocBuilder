<?php

function Usage()
{
    extract($GLOBALS);

    echo "Usage is:\n";
    for ($i = 0; ($opt = substr($Options->ShortOptions, $i * 2, 1)) != ""; ++$i)
    {
	echo sprintf("\t%-32s %s.\n",
		     "-".str_replace(":", "", $opt).
		     "/--".str_replace(":", "", $Options->LongOptions[$i]),
		     $Options->Description[$i]
	);
    }
    return (1);
}

