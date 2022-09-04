<div class="page" style="top: @@PAGEPOSITIONcm;">
    <table class="page_header">
	<tr><td class="left_align page_matter_logo" style="font-size: 12px;">
	    <?php if (@$DocBuilder->Configuration["Title"]["Top"]) { ?>
		<?=strtoupper(Translate($DocBuilder->Configuration["Title"])); ?> -
		<?php if (@$DocBuilder->Configuration["Title"]["PageNumber"]) { ?>
		    <?=$Page; ?>/@PAGECOUNT@
		<?php } ?>
	    <?php } ?>
	</td><td class="right_align page_activity_name">
	    <?=PrintImage($DocBuilder->Configuration, ["Company", "Logo"], ["Company", "Name"], "@@", "", false); ?>
	</td></tr>
    </table>
    <div class="page_content">
	<?php if ($Page == 1) { ?>

	    <?php if (@$DocBuilder->Configuration["Representative"]["PrintAddress"]) { ?>
		<div class="left_align" style="font-size: 12px; line-height: 120%;">
		    <?=MustPrint($DocBuilder->Configuration, ["Company", "Name"]); ?><br />
		    <?=MustPrint($DocBuilder->Configuration, ["Company", "Address"]); ?><br />
		    Tél. : <?=MustPrint($DocBuilder->Configuration, ["Company", "Tel"]); ?><br />
		    E-mail : <?=MustPrint($DocBuilder->Configuration, ["Company", "Mail"]); ?>
		</div>
	    <?php } ?>

	    <?php if (@$DocBuilder->Configuration["Customer"]["PrintAddress"]) { ?>
		<div class="right_align">
		    <?=Translate("To"); ?>
		    <?=MustPrint($DocBuilder->Configuration, ["Customer", "Name"]); ?><br />
		    <?=TryPrint($DocBuilder->Configuration, ["Customer", "Address"]); ?>
		</div>
		<br />
	    <?php } ?>

	    <div class="right_align" style="font-size: 12px;">
		<?=MustPrint($DocBuilder->Configuration, ["Company", "City"]); ?>,
		<?=Translate("the"); ?>
		<?=MustPrint($DocBuilder->Configuration, "GenerationDate"); ?>
	    </div>

	    <div class="letter_content">

	<?php } else { ?>

	    <div>

	<?php } ?>
