<?php

function Sidemark($text)
{
    if (is_array($text))
	$text = implode("", $text);
    echo "<div class=\"sidemark\">".ReadMarkdown($text)."</div>";
}

