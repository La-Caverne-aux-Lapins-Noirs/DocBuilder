<?php

function PrintTableContentEntry($ex, $num)
{
    ?>
    <tr><td class="index_entry_name">
	<?=PrintTitle($ex, $num); ?>
    </td><td class="index_entry_page">
	<?="!@@@".Translate($ex["Name"])."@@@!"; ?>
    </td></tr>
<?php
}
