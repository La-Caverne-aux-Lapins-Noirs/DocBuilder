<?php

function LanguageEncoder($str)
{
    $str = str_replace("À", "&Agrave;", $str);
    $str = str_replace("Â", "&Acirc;", $str);

    $str = str_replace("à", "&agrave;", $str);
    $str = str_replace("â", "&acirc;", $str);

    $str = str_replace("É", "&Eacute;", $str);
    $str = str_replace("È", "&Egrave;", $str);
    $str = str_replace("Ê", "&Ecirc;", $str);
    $str = str_replace("Ë", "&Euml;", $str);

    $str = str_replace("é", "&eacute;", $str);
    $str = str_replace("è", "&egrave;", $str);
    $str = str_replace("ê", "&ecirc;", $str);
    $str = str_replace("ë", "&euml;", $str);

    $str = str_replace("Î", "&Icirc;", $str);
    $str = str_replace("Ï", "&Iuml;", $str);

    $str = str_replace("î", "&icirc;", $str);
    $str = str_replace("ï", "&iuml;", $str);

    $str = str_replace("€", "&euro;", $str);

    //$str = utf8_encode($str);
    return ($str);
}

