<?php

function LoadDabsic($file)
{
    if (($json = @shell_exec("mergeconf -i $file -of .json 2> /dev/null")) == NULL)
	return (NULL);
    return ($json = @json_decode($json, true));
}

