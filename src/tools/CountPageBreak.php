<?php

function CountPageBreak()
{
    return (count(explode("@PAGEBREAK", GetBuffer())) - 1);
}
