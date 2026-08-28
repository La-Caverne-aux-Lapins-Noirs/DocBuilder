<?php

function DocBuilderAlignment($declaration, $content)
{
    return ("\\begingroup\\".$declaration."\n\n".
            $content."\n\n".
            "\\par\\endgroup\n");
}

/** Left-align a block, or switch to left alignment when used without content. */
function Left($rules)
{
    if (count($rules) == 1)
        return ("\\raggedright{}");
    return (DocBuilderAlignment("raggedright", $rules[1] ?? ""));
}
