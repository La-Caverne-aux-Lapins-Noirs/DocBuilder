<?php

function CLanguage($str)
{
    // Colorisateur de C

    // A la fin, on impose également le respect de l'indentation indiquée dans le fichier.
    $str = str_replace(" ", "&nbsp;", $str);
    return ($str);
}
