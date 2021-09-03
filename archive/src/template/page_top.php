<div class="page">
    <div class="page_margin page_top">
	<div class="matter_logo">
	    <?php if ($DocBuilder->Format == "PDF") { ?>
		<?php PrintImage($DocBuilder->Activity, "SmallMatterLogo",
				 "Matter", "<p>@@</p>", "", false);
		?>
	    <?php } else { ?>
		<?=MustPrint($DocBuilder->Activity, "Matter"); ?>
	    <?php } ?>
	</div>
	<div class="activity_name">
	    <?=MustPrint($DocBuilder->Activity, "Activity"); ?>
	</div>
    </div>
    <div class="page_margin page_content">
