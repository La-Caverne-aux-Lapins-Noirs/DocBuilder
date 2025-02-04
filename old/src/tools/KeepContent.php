<?php

$Buffer = "";

function KeepContent($buf)
{
    global $Buffer;

    $Buffer .= $buf;
    return ("");
}

