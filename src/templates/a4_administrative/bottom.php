	    <br /><br /><br />
	    @@KEEPLAST[
            <table class="signature">
		<tr><td class="left_align">
    		    <?php if (@$DocBuilder->Configuration["Customer"]["PrintSignatureForm"]) { ?>
			<?=Translate("DoneAt"); ?> <?=Repeat("&nbsp;", 10); ?>
			<?=Translate("DoneWhere"); ?> <?=Repeat("&nbsp;", 20); ?>.<br />
			<?=MustPrint($DocBuilder->Configuration, ["Customer", "Name"]); ?>
			<?php if (TryPrint($DocBuilder->Configuration, ["Customer", "Role"]) != "") { ?>
			    , <?=TryPrint($DocBuilder->Configuration, ["Customer", "Role"]); ?>
			<?php } ?>
			<br />
			<?=Translate("Signature"); ?>
		    <?php } ?>
		</td><td style="width: 20%;"></td></td><td class="left_align">
		    <?php if (@$DocBuilder->Configuration["Representative"]["PrintSignatureForm"]) { ?>
			<?=Translate("DoneAt"); ?>
			<?php if (strlen(@$DocBuilder->Configuration["Representative"]["Signature"])) { ?>
			    <?=$DocBuilder->Configuration["GenerationDate"]; ?>
			<?php } else { ?>
			    <?=Repeat("&nbsp;", 10); ?>
			<?php } ?>

			<?=Translate("DoneWhere"); ?>
			<?php if (strlen(@$DocBuilder->Configuration["Representative"]["Signature"])) { ?>
			    <?=MustPrint($DocBuilder->Configuration, ["Company", "City"]); ?>.<br />
			<?php } else { ?>
			    <?=Repeat("&nbsp;", 20); ?>.<br />
			<?php } ?>

			<?=MustPrint($DocBuilder->Configuration, ["Representative", "Name"]); ?>,
			<?=MustPrint($DocBuilder->Configuration, ["Representative", "Role"]); ?><br />
			<?=Translate("Signature"); ?>
			<?php if (strlen(@$DocBuilder->Configuration["Representative"]["Signature"])) { ?>
			    <br />
			    <img src="<?=$DocBuilder->Configuration["Representative"]["Signature"]; ?>" />
			<?php } ?>
		    <?php } ?>
		</td></tr>
  	    </table>
	    <?php if (isset($DocBuilder->Configuration["BottomNotice"])) { ?>
		<p class="bottom_notice"><?=Translate($DocBuilder->Configuration["BottomNotice"]); ?></p>
	    <?php } ?>
	    @@KEEPLAST]
        </div>
    </div>
    <div class="page_footer">
	<?php if (@$DocBuilder->Configuration["Initials"]) { ?>
	    <table style="width: 100%;"><tr><td style="width: 80%;">
	<?php } ?>
	    <?=MustPrint($DocBuilder->Configuration, ["Company", "LegalName"]); ?><br />
	    <?=strip_tags(MustPrint($DocBuilder->Configuration, ["Company", "LegalAddress"])); ?><br />
	    <?=MustPrint($DocBuilder->Configuration, ["Company", "Credentials"]); ?>
	<?php if (@$DocBuilder->Configuration["Initials"]) { ?>
	    </td><td style="border: 1px solid black;">
		<span style="font-size: smaller;">
		    <?=Translate("Initials"); ?>
		</span>
	    </td></tr></table>
	<?php } ?>
    </div>
</div>
