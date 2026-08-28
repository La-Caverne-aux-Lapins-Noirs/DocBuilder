<?php

function Right($rules)
{
    if (count($rules) == 1)
        return ("\\raggedleft{}");
    return (DocBuilderAlignment("raggedleft", $rules[1] ?? ""));
}
