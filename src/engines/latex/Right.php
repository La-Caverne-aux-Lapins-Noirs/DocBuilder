<?php

function Right($rules)
{
    $content = $rules[1] ?? "";

    return ("\\begin{flushright}\n".
            $content."\n".
            "\\end{flushright}\n");
}

