<?php

function IfC($rules)
{
    global $Configuration;

    if (($ref = Variable(["Variable", $rules[1]])) === "")
        return ("");

    $ope = $rules[2];
    $val = $rules[3];
    $res = false;

    if ($ope == "==")
        $res = ($ref == $val);
    else if ($ope == "!=")
        $res = ($ref != $val);
    else if ($ope == "<")
        $res = ($ref < $val);
    else if ($ope == "<=")
        $res = ($ref <= $val);
    else if ($ope == ">")
        $res = ($ref > $val);
    else if ($ope == ">=")
        $res = ($ref >= $val);
    else
        throw new RuntimeException("Unknown operator for IfC: " . $ope);

    if ($res)
        return ResolveDirectives($Configuration, $rules[4]);

    if (isset($rules[5]))
        return ResolveDirectives($Configuration, $rules[5]);

    return ("");
}

