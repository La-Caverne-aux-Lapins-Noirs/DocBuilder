<div class="page">
    <table class="page_header">
	<tr><td class="left_align page_matter_logo">
	    <?=PrintImage($DocBuilder->Activity, "SmallMatterLogo", "Matter", "@@", "", false); ?>
	</td><td class="right_align page_activity_name">
	    <?=MustPrint($DocBuilder->Activity, "Activity"); ?>
	</td></tr>
    </table>
    <?php if (isset($Ex["SideMark"])) { ?>
	<div class="page_sidemark">
	    <?=MustPrint($Ex, "SideMark"); ?>
	</div>
    <?php } ?>
    <div class="page_content<?=isset($Ex["SideMark"]) ? " page_shift" : ""; ?>">
