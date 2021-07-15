<?php
function Summary()
{
    global $DocBuilder;

    $Parts = [];
    foreach ($DocBuilder->Activity["Exercises"] as $act)
    {
	if (is_array($act))
	{
	    $Parts[] = $act["Name"];
	}
    }
    return ($Parts);
}

