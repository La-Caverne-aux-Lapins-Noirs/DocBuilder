<?php

function Usage()
{
    extract($GLOBALS);

    echo "Usage is:\n\n";
    for ($i = 0; ($opt = substr($Options->ShortOptions, $i * 2, 1)) != ""; ++$i)
    {
	echo sprintf("\t%-32s %s.\n",
		     "-".str_replace(":", "", $opt).
		     "/--".str_replace(":", "", $Options->LongOptions[$i]),
		     $Options->Description[$i]
	);
    }
    echo sprintf("\nCurrently supported types for activity files are:\n\n\t%s\n\n", implode("\n\t", array_keys($Types)));
    return (1);
}

