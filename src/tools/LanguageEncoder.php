<?php

function LanguageEncoder($str)
{
    $str = str_replace("é", "&eacute;", $str);
    $str = str_replace("è", "&egrave;", $str);
    $str = str_replace("ê", "&ecirc;", $str);
    $str = str_replace("à", "&agrave;", $str);
    $str = str_replace("â", "&acirc;", $str);
    //$str = utf8_encode($str);
    return ($str);
}
