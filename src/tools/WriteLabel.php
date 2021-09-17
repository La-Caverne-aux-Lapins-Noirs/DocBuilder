<?php

function WriteLabel($doc, $label)
{
    if (count($doc[$label]))
	echo "<b>".Translate($label)."</b>: ".implode(", ", $doc[$label])."<br />";
}
