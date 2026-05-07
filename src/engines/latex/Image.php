<?php

function	Image($rules)
{
    $str = "";
    $str .= "\includegraphics[";
    $str .= implode(",", array_slice($rules, 2));
    $str .= ",keepaspectratio]{{$rules[1]}}";
    return ($str);
}

