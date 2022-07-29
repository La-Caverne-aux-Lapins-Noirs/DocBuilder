<?php

function Base64Picture($picpath, $load = true)
{
    if ($load)
	$picpath = file_get_contents($picpath);
    $picpath = base64_encode($picpath);
    return ("data:image/png;base64,$picpath");
}
