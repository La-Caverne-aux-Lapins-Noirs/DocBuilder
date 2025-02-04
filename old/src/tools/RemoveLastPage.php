<?php

function RemoveLastPage($pdf)
{
    XSystem("pdftk $pdf~ cat 1-r2 output $pdf");
    XSystem("rm -f $pdf~");
}


