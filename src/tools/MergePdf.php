<?php

function MergePdf($out, array $list)
{
    $list = implode(" ", $list);
    XSystem("pdftk $list cat output $out");
}

