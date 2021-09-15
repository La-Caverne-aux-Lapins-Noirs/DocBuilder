<?php

function GetOption($opt, $long, $short, $default = NULL)
{
    if (isset($opt[$long]))
	return ($opt[$long]);
    if (isset($opt[$short]))
	return ($opt[$short]);
    return ($default);
}
