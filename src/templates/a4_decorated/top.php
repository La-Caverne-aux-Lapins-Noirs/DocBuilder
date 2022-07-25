<div class="page" style="top: @@PAGEPOSITIONcm;">
    <?php if ($Page != 1) { ?>

	<table class="page_header">
	    <tr><td class="left_align page_matter_logo">
		<?=PrintImage($DocBuilder->Configuration, ["Matter", "SmallLogo"], NULL, "@@", "", false); ?>
	    </td><td class="right_align page_activity_name">
		<?=MustPrint($DocBuilder->Configuration, "Matter"); ?>
	    </td></tr>
	</table>

	<?php if (isset($Ex["Document"]["SideMark"])) { ?>
	    <div class="page_sidemark">
		<?=MustPrint($Ex, ["Document", "SideMark"]); ?>
	    </div>
	<?php } ?>

	<div class="page_content <?=isset($Ex["Document"]["SideMark"]) ? "page_shift" : ""; ?>">

    <?php } else { ?>

    <?php } ?>
