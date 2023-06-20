	    <br /><br />
	    @@KEEPLAST[
        	<?=Translate("DoneWhere"); ?>
        	<?=MustPrint($DocBuilder->Configuration, ["Company", "City"]); ?>,
        	<?php if (@$DocBuilder->Configuration["Customer"]["PrintSignatureForm"] && @$DocBuilder->Configuration["Representative"]["PrintSignatureForm"]) { ?>
        	<?=Translate("DoubleDoc"); ?>,
        	<?php } ?>
        	<br /><br />
        	<?=Translate("DoneAt"); ?>
        	<?=MustPrint($DocBuilder->Configuration, "GenerationDate"); ?><br /><br />
        	
        	<?php if (@$DocBuilder->Configuration["Customer"]["PrintSignatureForm"] && @$DocBuilder->Configuration["Representative"]["PrintSignatureForm"]) { ?>
        	<table class="signature" style="border: solid 1px; height: 300px;">
        	<?php } else { ?>
            <table class="signature">
			<?php } ?>
			<?php if (@$DocBuilder->Configuration["Customer"]["PrintSignatureForm"] && @$DocBuilder->Configuration["Representative"]["PrintSignatureForm"]) { ?>
		<tr><td class="left_align signatureTop" style="border: solid 1px; height: 300px; padding-left: 5px;">
			<?php } else { ?>
		<tr><td class="left_align signatureTop">
			<?php } ?>
    		<?php if (@$DocBuilder->Configuration["Customer"]["PrintSignatureForm"]) { ?>
    		<?=Translate("SignatureClient"); ?><br /><br />
			<?=MustPrint($DocBuilder->Configuration, ["Customer", "Name"]); ?>
			<?php if (TryPrint($DocBuilder->Configuration, ["Customer", "Role"]) != "") { ?>
			    , <?=TryPrint($DocBuilder->Configuration, ["Customer", "Role"]); ?>
			<?php } ?>
		    <?php } ?>
		</td><td style="width: 1%;"></td></td><td class="left_align signatureTop">
		    <?php if (@$DocBuilder->Configuration["Representative"]["PrintSignatureForm"]) { ?>

			<?=Translate("SignatureCompany"); ?><br /><br />
			<?=MustPrint($DocBuilder->Configuration, ["Representative", "Name"]); ?>,
			<?=MustPrint($DocBuilder->Configuration, ["Representative", "Role"]); ?><br />
			<?=PrintImage($DocBuilder->Configuration, ["Representative", "Signature"], NULL, "@@", 'width="100"', false); ?>
		    <?php } ?>
		</td></tr>
  	    </table>
	    <?php if (isset($DocBuilder->Configuration["BottomNotice"])) { ?>
		<p class="bottom_notice"><?=Translate($DocBuilder->Configuration["BottomNotice"]); ?></p>
	    <?php } ?>
	    @@KEEPLAST]
        </div>
    </div>
    <div class="page_footer" style="width: 80%; text-align: left;">
	<?php if (@$DocBuilder->Configuration["Initials"]) { ?>
	    <table style="width: 100%;"><tr><td style="width: 80%;">
	<?php } ?>
	    <?=MustPrint($DocBuilder->Configuration, ["Company", "LegalName"]); ?>,
	    <?=strip_tags(MustPrint($DocBuilder->Configuration, ["Company", "LegalAddress"])); ?>
	    <?=MustPrint($DocBuilder->Configuration, ["Company", "Credentials"]); ?>
	<?php if (@$DocBuilder->Configuration["Initials"]) { ?>
	    </td><td style="border: 1px solid black; text-align: center;">
		<span style="font-size: smaller;">
		    <?=Translate("Initials"); ?>
		</span>
	    </td></tr></table>
	<?php } ?>
    </div>
</div>
