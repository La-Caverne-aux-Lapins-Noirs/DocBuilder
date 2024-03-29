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

    $str = str_replace("Ô", "&Ocirc;", $str);
    $str = str_replace("Ö", "&Ouml;", $str);

    $str = str_replace("ô", "&ocirc;", $str);
    $str = str_replace("ö", "&ouml;", $str);

    $str = str_replace("ù", "&ugrave;", $str);
    $str = str_replace("û", "&ucirc;", $str);
    $str = str_replace("ü", "&uuml;", $str);

    $str = str_replace("Ù", "&Ugrave;", $str);
    $str = str_replace("Û", "&Ucirc;", $str);
    $str = str_replace("Ü", "&Uuml;", $str);

    $str = str_replace("ç", "&ccedil;", $str);
    $str = str_replace("œ", "&oelig;", $str);

    $str = str_replace("€", "&euro;", $str);

    $str = str_replace("’", "&apos;", $str);

    $str = str_replace("«", "&#171;", $str);
    $str = str_replace("»", "&#187;", $str);

    $str = str_replace("–", "&ndash;", $str);

    //$str = utf8_encode($str);
    return ($str);
}

