<?php StartSubRecord(); ?>

<p class="center_align">
    <?=PrintMarkup($DocBuilder->Activity, "FrontMessage", '<div class="front_message">@@</div>'); ?>
</p>

<?php if (count($DocBuilder->GlobalMedals) > 0) { ?>
    <div id="global_medal_list">
	<?php require ("medal_list.php"); ?>
    </div>
<?php } ?>

<p class="center_align">
    <?=Translate("Copyright"); ?>
</p>

<?php StopSubRecord(); ?>
