<?php

function KeepContent($buf)
{
    global $DocBuilder;

    $DocBuilder->Output .= $buf;
    return ("");
}

