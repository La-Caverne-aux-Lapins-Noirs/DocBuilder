<?php if (isset($Ex["Medals"])) $Ex["Medal"] = $Ex["Medals"]; ?>
<?php if (!isset($Ex["Medal"])) return ; ?>
<?php if (!is_array($Ex["Medal"])) $Ex["Medal"] = [$Ex["Medal"]]; ?>
<br /><br />
<table class="medal_list">
    <?php $i = 0; ?>
    <?php foreach ($Ex["Medal"] as $medal) { ?>
	<?php if ($i % 2 == 0) { ?>
	    <tr><td>
		<?php PrintMedal($medal); ?>
	    </td>
	<?php } else { ?>
	    <td>
		<?php PrintMedal($medal); ?>
	    </td></tr>
	<?php } $i += 1; ?>
    <?php } ?>
</table>
