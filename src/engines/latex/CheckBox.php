<?php

function	CheckBox($rules)
{
    if (@$rules[2])
	print_r($rules);
    if (isset($rules[1]) && $rules[1] != "")
	return ("\\checkedbox\\nobreakspace");
    return ("\\checkbox\\nobreakspace");
}

