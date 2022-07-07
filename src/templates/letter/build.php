<?php StartSubRecord(true); ?>
<?php if (@$DocBuilder->Configuration["Title"]["Big"]) { ?>
    <h1 style="text-align: center;"><?=Translate($DocBuilder->Configuration["Title"]); ?></h1>
    <br />
    <br />
<?php } else { ?>
    <p>
	<br />
	<b>
	    <?=Translate("Object"); ?> :
	    <?=Translate($DocBuilder->Configuration["Title"]); ?>
	</b>
	<br />
	<br />
    </p>
<?php } ?>
<p>
    <?=Translate($DocBuilder->Configuration["Content"]); ?>
</p>
<?php StopSubRecord(true); ?>
