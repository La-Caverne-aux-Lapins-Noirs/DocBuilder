<?php
require ("title.php");
require ("concerned_files.php");
echo Translate($Ex["Document"]);
require ("medal_list.php");

// 2 règles par centimètres environs

?>

<?php if (@$Ex["Norm"]["Describe"]) { ?>
    <br />
    <p style="text-align: center; font-size: large;"><?=Translate("Summary"); ?></p>
    <br />
    <?php
    $heights = [12, 22, 22];
    $rules = display_norm($Ex["Norm"]);
    ?>
    <?php for ($r = 0, $page = 0; $r < count($rules); $page += 1) { ?>
	<div class="_3columns small_text" style="height: <?=$heights[$page]; ?>cm; width: 100%;">
	    <?php for ($rr = 0; $rr < 1.5 * $heights[$page] && $r < count($rules); ++$rr, ++$r) { ?>
		<?=$rules[$r]; ?>
	    <?php } ?>
	</div>
	<?php if ($r < count($rules)) echo "@PAGEBREAK"; ?>
    <?php } ?>
<?php } ?>
