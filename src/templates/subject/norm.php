<?php
$Alternate = false;

function display_norm($nod, $alt = true)
{
    global $DocBuilder;
    global $Alternate;

    if (!is_array($nod))
	return ;
    if (isset($nod["NoDoc"]))
	return ;
    // On est sur une description
    if (isset($nod[$DocBuilder->Language]))
	return (Translate($nod));
    $rules = [];
    foreach ($nod as $k => $n)
    {
	if (!is_array($n))
	    continue ;
	$Alternate = $alt ? !$Alternate : true;
	if (is_array($ret = display_norm($n)))
	    $rules = array_merge($rules, $ret);
	else if ($ret != "")
	    $rules[] = "<p class=\"".($Alternate ? "" : "gray")."\">@ALINEA@$ret</p>";
    }
    return ($rules);
}


