<?php foreach ($DocBuilder->GlobalMedals as $medal) { ?>
    <?php if ($medal["Type"] == "coin") { ?>
	<div
	    class="medal_coin"
	    style="background-image: url('<?=$medal["Icon"]; ?>');"
	    title="<?=Translate($medal["Name"]); ?>"
	></div>
    <?php } ?>
<?php } ?>
<?php foreach ($DocBuilder->GlobalMedals as $medal) { ?>
    <?php if ($medal["Type"] == "band") { ?>
	<div
	    class="medal_band"
	    style="background-image: url('<?=$medal["Icon"]; ?>');"
	    title="<?=Translate($medal["Name"]); ?>"
	></div>
    <?php } ?>
<?php }
