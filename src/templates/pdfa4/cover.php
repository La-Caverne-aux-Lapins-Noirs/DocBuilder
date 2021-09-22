<div class="page">
    <div class="page_content">
	<div id="banner">
	    <div id="codename">
		<?=TryPrint($DocBuilder->Instance, "CodeName"); ?>
	    </div>
	    <div id="school_logo">
		<?=PrintImage($DocBuilder->Configuration, "SchoolLogo", "School", "<h1>@@</h1>", "", false); ?>
	    </div>
	    <div id="activity_logo">
		<?=PrintImage($DocBuilder->Activity, "ActivityLogo", "Activity", "<h1>@@</h1>", "", false); ?>
	    </div>
	</div>

	<div id="matter_logo">
	    <?=PrintImage($DocBuilder->Activity, "MatterLogo", "Matter", "<h1>@@</h1>", "", false); ?>
	</div>

	<p id="activity_info">
	    <?php
	    if (($rev = TryPrint($DocBuilder->Instance, "Manager")) != "")
		echo "<b>".Translate("Manager")."</b>: ".$rev."<br />";
	    if (($rev = TryPrint($DocBuilder->Instance, "Mail")) != "")
		echo "<b>".Translate("Mail")."</b>: <".$rev."><br />";
	    if (($rev = TryPrint($DocBuilder->Activity, "Author")) != "")
		echo "<b>".Translate("Author")."</b>: ".$rev."<br />";
	    if (($rev = TryPrint($DocBuilder->Activity, "Revision")) != "")
		echo "<b>".Translate("Revision")."</b>: ".$rev."<br />";
	    if (($rev = TryPrint($DocBuilder->Activity, "LastRevision")) != "")
		echo "<b>".Translate("LastRevision")."</b>: ".$rev."<br />";
	    ?>
	</p>
    </div>
</div>
