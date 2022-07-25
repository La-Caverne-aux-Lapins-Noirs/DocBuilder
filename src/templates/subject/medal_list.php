<?php if (!isset($Ex["Medal"])) return ; ?>
<?php if (!is_array($Ex["Medal"])) $Ex["Medal"] = [$Ex["Medal"]]; ?>
<?php
function print_medal($m)
{
    $picture = @XShellExec("genicon $m");
    $text = @json_decode(XShellExec("genicon -t $m"));
?>
    <div class="medal_picture" style="background-image: url('<?=$picture; ?>');"></div>
    <div class="medal_description">
	<h5><?=@$text["name"]; ?></h5>
	<p><?=@$text["description"]; ?></p>
    </div>
<?php
}
?>
<table class="medal_list">
    <?php $i = 0; ?>
    <?php foreach ($Ex["Medal"] as $medal) { ?>
	<?php if ($i % 2 == 0) { ?>
	    <tr><td>
		<?php print_medal($medal); ?>
	    </td>
	<?php } else { ?>
	    <td>
		<?php print_medal($medal); ?>
	    </td></tr>
	<?php } $i + =1; ?>
    <?php } ?>
</table>
