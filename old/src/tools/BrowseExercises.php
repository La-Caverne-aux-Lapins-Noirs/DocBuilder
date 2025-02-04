<?php

function BrowseExercises($exercises, $func)
{
    $cnt = 1;
    foreach ($exercises as $i => $ex)
    {
	if (isset($ex["NoDoc"]))
	    continue ;

	if (IsArray($ex))
	    BrowseExercises($ex, $func);
	else
	    $func($ex, [$i + 1]);
	$cnt += 1;
    }
    return ($cnt);
}
