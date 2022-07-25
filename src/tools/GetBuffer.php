<?php

function GetBuffer()
{
    global $Buffer;

    return ($Buffer.ob_get_contents());
}
