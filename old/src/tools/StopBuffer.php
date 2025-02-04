<?php

function StopBuffer()
{
    global $Buffer;

    $tmp = $Buffer.ob_get_contents();
    ob_end_clean();
    $Buffer = "";
    return ($tmp);
}

