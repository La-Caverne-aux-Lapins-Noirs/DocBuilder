<?php

function PrintTableContentEntry($ex, $num)
{
    echo "<div class=\"index_entry\">";
    echo "<div class=\"index_entry_name\">";
    PrintTitle($ex, $num);
    echo "</div>";
    echo "<div class=\"index_entry_page\">";
    echo "!@@@".Translate($ex["Name"])."@@@!";
    echo "</div>";
    echo "</div>";
}
