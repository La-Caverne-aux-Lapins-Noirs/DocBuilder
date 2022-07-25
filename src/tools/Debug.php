<?php

function ekko($data)
{
    return (fprintf(STDERR, "%s\n", print_r($data, true)));
}

function Debug($data, $ctx = "Global")
{
    global $DocBuilder;

    $DocBuilder->Debugs[] = "$ctx: ".print_r($data, true);
}

