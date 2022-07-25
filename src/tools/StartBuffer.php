<?php

function StartBuffer()
{
    global $Buffer;

    $Buffer = "";
    ob_start("KeepContent");
}

