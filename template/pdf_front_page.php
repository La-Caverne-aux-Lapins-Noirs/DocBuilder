<div class="page">
    <div class="page_margin page_top">
	<div id="subject_id">
	    <?=strtoupper(MustPrint($DocBuilder->Instance, 'ActivityKey')); ?>
	</div>
	<div id="deadline">
	    <?=Translate("Deadline"); ?>:
	    <?=date("d/m/Y H:i:s", (int)MustPrint($DocBuilder->Instance, "DeliveryDate")); ?>
	</div>
    </div>

  <div class="page_margin page_content">
      <div class="huge_matter_logo">
	  <?php PrintImage($DocBuilder->Activity, "MatterLogo", "Matter", "<h2>@@</h2>", "", false); ?>
      </div>
      <div class="huge_school_logo">
	  <?php PrintImage($DocBuilder->Configuration, "SchoolLogo", "School", "<h1>@@</h1>", "", false); ?>
      </div>
      <div class="huge_activity_logo">
	  <?php PrintImage($DocBuilder->Activity, "ActivityLogo", "Activity", "<h3>@@</h3>", "", false); ?>
      </div>
      <?php PrintMarkup($DocBuilder->Activity, "FrontMessage", '<div class="front_message">@@</div>', true, false); ?>
      <?php require_once ("pdf_global_medals.php"); ?>
  </div>

  <div class="page_margin page_bottom">
      <?php
      PrintMarkup($DocBuilder->Activity, "Author", '<div class="author">'.Translate("Author").': @@</div>');
      PrintMarkup($DocBuilder->Activity, "Revision", '<div class="revision">'.Translate("Revision").': @@</div>');

      PrintMarkup($DocBuilder->Activity, "Manager", '<div class="manager">'.Translate("Manager").': @@</div>');
      PrintMarkup($DocBuilder->Activity, "Mail", '<div class="mail">'.Translate("Mail").': @@</div>');
      ?>
  </div>
</div>
