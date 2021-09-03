<?php

function AddChapter($ex, $num)
{
    extract($GLOBALS);

    StartSubRecord();
    if (AddInstruction($ex, $num) == false)
	return (false);
    StopSubRecord();
    return (true);
}

