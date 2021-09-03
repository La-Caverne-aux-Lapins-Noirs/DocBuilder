<?php
if (count($Medals = $DocBuilder->GlobalMedals) > 0)
{
?>
<div class="front_medal_list">
    <div class="front_medal_title">
	<?=Translate("MedalToAcquire"); ?>:
    </div>
    <div>
	<?php
	foreach ($Medals as $med => $unused)
	{
	?>
	    <div
		class="front_medal_<?=$Medals[$med]["Type"]; ?>"
		style="background-image: url(<?=$Medals[$med]["Icon"]; ?>)"
		title="<?=Translate($Medals[$med]["Name"]); ?>"
	    ></div>
	    <?php
	}
	?>
    </div>
</div>
<?php
}
?>
