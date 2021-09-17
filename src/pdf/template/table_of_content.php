
<h2><?=MustPrint($DocBuilder->Dictionnary, "Index"); ?></h2>

<?php

function PrintTableOfContent($ex, $num)
{
    if (count($num) < 5)
	echo "<h".(count($num) + 1)." style=\"left-margin: ".(count($num) / 2)."cm\">";
    else
	echo "<p style=\"left-margin: 3cm\">";

    echo MergeNumber($num);
    echo MustPrint($ex, "Name");

    if (count($num) < 5)
	echo "</h".(count($num) + 1).">";
    else
	echo "</p>";
}

$Depth = [];
BrowseExercises($Ex, $Depth);

