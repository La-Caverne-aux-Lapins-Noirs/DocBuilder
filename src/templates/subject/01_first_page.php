
<div class="header_block">
    <div class="matter">
	<?=$DocBuilder->Configuration["CodeName"]; ?>
	<?=Translate($DocBuilder->Configuration["Matter"]); ?>
    </div>

    <div class="key">
	<?=$DocBuilder->Configuration["Token"]; ?>
    </div>
</div>

<div
    class="title_block"
    <?php if (@strlen($DocBuilder->Configuration["BandBackground"])) { ?>
	   style="background: <?=$DocBuilder->Configuration["BandBackground"]; ?>;"
    <?php } ?>
>
    <div
	class="matter_logo"
	<?php if (($Picture = isset($DocBuilder->Configuration["Matter"]["Logo"]))) { ?>
	    style="background-image: <?=PrintImage($DocBuilder->Configuration, ["Matter", "Logo"], NULL, "", "", true, "Global", true); ?>;"
	<?php } ?>
    >
	<?php if (!$Picture) { ?>
	    <?=Translate($DocBuilder->Configuration["Matter"]); ?>
	<?php } ?>
    </div>
    <div
	class="activity_logo"
	<?php if (($Picture = isset($DocBuilder->Configuration["Activity"]["Logo"]))) { ?>
	    style="background-image: <?=PrintImage($DocBuilder->Configuration, ["Activity", "Logo"], NULL, "", "", true, "Global", true); ?>;"
	<?php } ?>
    >
	<?php if (!$Picture) { ?>
	    <?=Translate($DocBuilder->Configuration["Activity"]); ?>
	<?php } ?>
    </div>
</div>

<?php if (isset($DocBuilder->Configuration["FrontPage"]["Message"])) { ?>
    <div class="one_liner">
	<b><?=Translate($DocBuilder->Configuration["FrontPage"]["Message"]); ?></b>
    </div>
<?php } ?>

<div class="description">
    <?php if (isset($DocBuilder->Configuration["FrontPage"]["Description"])) { ?>
	<i><?=Translate($DocBuilder->Configuration["FrontPage"]["Description"]); ?></i>
    <?php } ?>
</div>

<div class="footer_block">
    <div class="metadata">
	<?php if (@strlen($Text = GetIdentities($DocBuilder->Configuration["Document"]["Author"]))) { ?>
	    <b><?=Translate("Author"); ?>:</b><br />
	    <?=$Text ?>
	<?php } ?>
	<?php if (@strlen($Text = GetIdentities($DocBuilder->Configuration["Matter"]["Teacher"]))) { ?>
	    <b><?=Translate("ModuleTeacher"); ?>:</b><br />
	    <?=$Text; ?>
	<?php } ?>
	<?php if (@strlen($Text = GetIdentities($DocBuilder->Configuration["Activity"]["Teacher"]))) { ?>
	    <b><?=Translate("ActivityTeacher"); ?>:</b><br />
	    <?=$Text; ?>
	<?php } ?>

	<b><?=Translate("CurrentVersion"); ?>:</b>
	<ul>
	    <li><?=$DocBuilder->Configuration["Document"]["Revision"]; ?>
		(<?=$DocBuilder->Configuration["Document"]["LastRevision"]; ?>)
	    </li>
	</ul>
    </div>
    <div
	class="company_logo"
	<?php if (($Picture = isset($DocBuilder->Configuration["Company"]["Logo"]))) { ?>
	    style="background-image: <?=PrintImage($DocBuilder->Configuration, ["Company", "Logo"], NULL, "", "", true, "Global", true); ?>;"
	<?php } ?>
    >
	<?php if (!$Picture) { ?>
	    <?=Translate($DocBuilder->Configuration["Company"]["Name"]); ?>
	<?php } ?>
    </div>
</div>

<div class="no_share">
    <?=Translate("DoNotShare"); ?>
</div>

<div class="company_block">
    <?=Translate($DocBuilder->Configuration["Company"]["LegalName"]); ?> 2022-<?=date("Y", time()); ?>
</div>
