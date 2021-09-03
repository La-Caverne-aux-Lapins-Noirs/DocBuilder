<?php

function Usage()
{
    extract($GLOBALS);

    echo "{$Options->Normal}Usage is:\n";
    for ($i = 0; isset($Options->ShortOptions[$i * 2]); ++$i)
	fprintf(STDOUT,
		"\t%-32s %s.\n",
		"-".str_replace(":", "", $Options->ShortOptions[$i * 2]).
		"/--".str_replace(":", "", $Options->LongOptions[$i]),
		$Options->Description[$i]
	);
    return (1);
}

