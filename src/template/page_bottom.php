  </div> <?php // page_content ; ?>
  <div class="page_margin page_bottom">
      <div class="activity_logo">
	  <?php if ($DocBuilder->Format == "PDF") { ?>
	      <?php PrintImage($DocBuilder->Activity, "SmallActivityLogo", "Activity", "<div>@@</div>", "", false); ?>
	  <?php } else { ?>
	      <?=MustPrint($DocBuilder->Activity, "Activity"); ?>
	  <?php } ?>
      </div>
      <div class="school_logo">
	  <?php if ($DocBuilder->Format == "PDF") { ?>
	      <?php PrintImage($DocBuilder->Configuration, "SmallSchoolLogo", "School", "<div>@@</div>", "", false); ?>
	  <?php } else { ?>
	      <?=MustPrint($DocBuilder->Activity, "School"); ?>
	  <?php } ?>
      </div>
      <div class="watermark">
	  <?=Translate("GeneratedFor"); ?> <?=$DocBuilder->Instance["FullName"]; ?> - <?=Translate("DoNotDistribute"); ?>
      </div>
      <div class="page_counter">
	  <?=$PageCount++ + 1; ?> / @PAGECOUNT@
      </div>
  </div>
</div><?php // page ; ?>

