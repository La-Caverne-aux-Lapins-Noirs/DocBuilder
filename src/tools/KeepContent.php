<?php

$Buffer = "";

function KeepContent($buf)
{
    global $Buffer;
    global $Configuration;

    $Buffer .= $buf;
    return ("");
}

