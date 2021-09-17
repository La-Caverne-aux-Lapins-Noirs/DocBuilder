<?php

function BrowseExercises($exercises, &$depth, $func)
{
    $cnt = 1;
    foreach ($exercises as $ex)
    {
	if (isset($ex["NoDoc"]))
	    continue ;

	// Le tableau dispose de plusieurs dimensions afin
	// de pouvoir chainer des exercices ou actions spécifiques liés.
	// Par exemple: compiler un exercice, puis le tester.
	// Par exemple: verifier la norme d'un exercice, puis le tester.
	// Etc.
	$sub = ArrayPush($depth, $cnt);
	if (IsArray($ex))
	    BrowseExercises($ex, $sub, $func);
	else
	    $func($ex, $sub);
	$cnt += 1;
    }
    return ($cnt);
}
