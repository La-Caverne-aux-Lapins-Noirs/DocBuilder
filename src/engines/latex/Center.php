<?php

function Center($rules)
{
    // Historical declaration form used by existing models.
    if (count($rules) == 1)
        return ("\\centering");
    return (DocBuilderAlignment("centering", $rules[1] ?? ""));
}
