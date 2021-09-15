<div class="page">
    <table class="page_header">
	<tr><td class="left_align page_matter_logo">
	    <?=PrintImage($DocBuilder->Activity, "SmallMatterLogo", "Matter", "@@", "", false); ?>
	</td><td class="right_align page_activity_name">
	    <?=MustPrint($DocBuilder->Activity, "Activity"); ?>
	</td></tr>
    </table>
    <?php if ($Ex["SideMark"]) { ?>
	<div class="page_sidemark">
	    <?=MustPrint($D, "Sidemark"); ?>
	</div>
    <?php } ?>
    <div class="page_content <?=$Ex["SideMark"] ? "page_shift" : ""; ?>"
