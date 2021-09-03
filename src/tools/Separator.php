<?php

function Separator($str)
{
    extract($GLOBALS);

    if ($DocBuilder->Code == "html")
    {
	echo "<!-- ============================================== -->\n";
	echo "<!-- $str -->\n";
    }
    else if ($DocBuilder->Code == "latex")
    {
	echo "% =================================================\n";
	echo "% $str\n";
    }
}

