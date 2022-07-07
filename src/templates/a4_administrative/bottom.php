            <br /><br />
            <table style="width: 100%;">
		<tr><td class="left_align">
    		    <?php if (@$DocBuilder->Configuration["Customer"]["PrintSignatureForm"]) { ?>
			<?=Translate("DoneAt"); ?> <?=Repeat("&nbsp;", 20); ?>
			<?=Translate("DoneWhere"); ?> <?=Repeat("&nbsp;", 30); ?>.<br />
			<?=MustPrint($DocBuilder->Configuration, ["Customer", "Name"]); ?>
			<?php if (TryPrint($DocBuilder->Configuration, ["Customer", "Role"]) != "") { ?>
			    , <?=TryPrint($DocBuilder->Configuration, ["Customer", "Role"]); ?>
			<?php } ?>
			<br />
			<?=Translate("Signature"); ?>
		    <?php } ?>
		</td><td class="right_align">
		    <?php if (@$DocBuilder->Configuration["Representative"]["PrintSignatureForm"]) { ?>
			<?=Translate("DoneAt"); ?> <?=$DocBuilder->Configuration["GenerationDate"]; ?>
			<?=Translate("DoneWhere"); ?> <?=MustPrint($DocBuilder->Configuration, ["Company", "City"]); ?>.<br />
			<?=MustPrint($DocBuilder->Configuration, ["Representative", "Name"]); ?>,
			<?=MustPrint($DocBuilder->Configuration, ["Representative", "Role"]); ?><br />
			<?=Translate("Signature"); ?>
			<?php if (strlen(@$DocBuilder->Configuration["Representative"]["Signature"])) { ?>
			    <img src="<?=$DocBuilder->Configuration["Representative"]["Signature"]; ?>" />
			<?php } ?>
		    <?php } ?>
		</td></tr>
  	    </table>
        </div>
    </div>
    <div class="page_footer">
	<?=MustPrint($DocBuilder->Configuration, ["Company", "LegalName"]); ?><br />
	<?=strip_tags(MustPrint($DocBuilder->Configuration, ["Company", "Address"])); ?><br />
	<?=MustPrint($DocBuilder->Configuration, ["Company", "Credentials"]); ?>
    </div>
</div>
