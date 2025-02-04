<div class="concerned_files">
    <?php if (!isset($Ex["Directory"]) || $Ex["Directory"] == "") $Ex["Directory"] = "."; ?>
    <b><?=Translate("Directory"); ?></b>: <?=$Ex["Directory"] == "." ? Translate("DeliveryRootDirectory") : $Ex["Directory"]; ?>
    <?php if (isset($Ex["Files"])) { ?>
	<br />
	<?php if (!is_array($Ex["Files"])) $Ex["Files"] = [$Ex["Files"]]; ?>
	<b><?=Translate("CheckedFiles"); ?></b>:
	<?php
	$gen = [];
	foreach ($Ex["Files"] as $f)
	{
	    if (substr($f, 0, 1) == '!')
		$gen[] = Translate("AllFilesMatching")." ".substr($f, 1);
	    else
		$gen[] = $f;
	}
	echo implode(", ", $gen);
	?>
    <?php } ?>

    <?php if (isset($Ex["Artefacts"])) { ?>
	<br />
	<?php if (!is_array($Ex["Artefacts"])) $Ex["Artefacts"] = [$Ex["Artefacts"]]; ?>
	<b><?=Translate("CheckedArtefacts"); ?></b>:
	<?php
	$gen = [];
	foreach ($Ex["Artefacts"] as $f)
	{
	    if (substr($f, 0, 1) == '!')
		$gen[] = Translate("AllFilesMatching")." ".substr($f, 1);
	    else
		$gen[] = $f;
	}
	echo implode(", ", $gen);
	?>
    <?php } ?>
</div>
