<?php

function WriteTable($doc, $fil)
{
    if (count($fil) == 0)
	return ;
    $sum = 0;
    foreach ($fil as $f)
	if (isset($doc[$f]))
	    $sum += count($doc[$f]);
    if ($sum == 0)
	return ;
    echo "<table><tr>";
    foreach ($fil as $f)
	echo "<th>".Translate($f)."</th>";
    echo "</tr><tr>";
    foreach ($fil as $f)
	echo "<td>".implode("<br />", $doc[$f])."</td>";
    echo "</tr></table>";
}

