<?php

function RemoveLastPage($pdf)
{
    XSystem("pdftk $pdf~ cat 1-r2 output $pdf");
}

