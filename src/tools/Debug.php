<?php

function Debug($data, $ctx = "Global")
{
    global $DocBuilder;
    
    $DocBuilder->Debugs[] = "$ctx: ".print_r($data, true);
}

