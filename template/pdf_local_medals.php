<?php
if (isset($Exercise["Medals"]) && count($Medals = $Exercise["Medals"]) > 0)
{
?>
<div class="exercise_medal_list">
    <div class="exercise_medal_title">
	<?=Translate("MedalAvailable"); ?>:
    </div>
    <?php
    foreach ($Medals as $name)
    {
	if (isset($DocBuilder->Instance["AcquiredMedals"]))
	    $Acquired = (array_search($name, $DocBuilder->Instance["AcquiredMedals"]) !== false);
	else
	    $Acquired = false;
	$MedalInfo = RetrieveMedal($name);
	if ($MedalInfo["Type"] == "coin")
	{
    ?>
	<div class="exercise_medal_coin">
	    <div
		class="exercise_medal_coin_icon"
		style="background-image: url(<?=$MedalInfo["Icon"]; ?>);"
		title="<?=Translate($MedalInfo["Name"]); ?>"
	    ></div>
	    <div class="exercise_medal_coin_text">
		<h5><?=Translate($MedalInfo["Name"]); ?></h5>
		<p><?=Translate($MedalInfo["Description"]); ?></p>
	    </div>
	    <?php if ($Acquired) { ?>
		<div class="medal_acquired">&nbsp;</div>
	    <?php } ?>
	</div>
    <?php
        }
        else
        {
    ?>
	<div class="exercise_medal_band">
	    <div
		class="exercise_medal_band_icon"
		style="background-image: url(<?=$MedalInfo["Icon"]; ?>);"
		title="<?=Translate($MedalInfo["Name"]); ?>"
	    ></div>
	    <?php if ($Acquired) { ?>
		<div class="medal_acquired">&nbsp;</div>
	    <?php } ?>
	</div>
    <?php
        }
    }
    ?>
</div>
<?php
}
?>
