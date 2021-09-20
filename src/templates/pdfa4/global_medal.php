<?php StartSubRecord(); ?>

<?=PrintMarkup($DocBuilder->Activity, "FrontMessage", '<div class="front_message">@@</div>'); ?>
<?=Translate("Copyright"); ?>

<?php if (count($DocBuilder->GlobalMedals) > 0) { ?>
    <div id="global_medal_list">
	<?php require ("medal_list.php"); ?>
    </div>
<?php } ?>

<?php StopSubRecord(); ?>
