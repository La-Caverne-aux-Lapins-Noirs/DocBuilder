<?php

function MergeData(&$cnf)
{
    $cnf = implode("", MustBeAnArray($cnf));
    return ($cnf);
}

