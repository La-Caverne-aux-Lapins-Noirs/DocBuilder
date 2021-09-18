<?php

function WriteLabel($doc, $label)
{
    if (isset($doc[$label]) && count($doc[$label]))
	echo "<b>".Translate($label)."</b>: ".implode(", ", $doc[$label])."<br />";
}
