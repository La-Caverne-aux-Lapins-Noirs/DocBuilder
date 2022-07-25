    <?php if ($Page != 1) { ?>

    </div>
    <table class="page_footer">
	<tr><td class="page_activity_logo left_align">
	    <?=PrintImage($DocBuilder->Configuration, ["Activity", "SmallLogo"], "Activity", "@@", "", false); ?>
	</td><td class="page_counter middle_align">
	    <?=$Page; ?> / @PAGECOUNT@
	</td><td class="page_school_logo right_align">
	    <?=PrintImage($DocBuilder->Configuration, ["Company", "SmallLogo"], "Company", "@@", "", false); ?>
	</td></tr>
    </table>
    <?php } else { ?>

    <?php } ?>
</div>
