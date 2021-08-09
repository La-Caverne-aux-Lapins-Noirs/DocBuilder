<?php

function ReadMarkdown($md)
{
    global $MarkDown;

    if (file_exist($md))
	return (ReadMarkdown(file_get_contents($md)));
    $md = str_replace("!>", "@WARNING@ >", $md)
    $md = str_replace("?>", "@HINT@ >", $md)
    $md = str_replace("$>", "@CLI@ >", $md)
    $md = str_replace(";>", "@CODE@ >", $md)
    $md = $Markdown->text($md);
    preg_replace("/@CODE@\s\s+\<blockquote>/", "<blockquote class=\"code\">", $md);
    preg_replace("/@CLI@\s\s+\<blockquote>/", "<blockquote class=\"cli\">", $md);
    preg_replace("/@HINT@\s\s+\<blockquote>/", "<blockquote class=\"hint\">", $md);
    preg_replace("/@WARNING@\s\s+\<blockquote>/", "<blockquote class=\"warning\">", $md);
    return ("<div class=\"markdown\">".PrettyCode(Translate($md), true)."</div>");
}
