<?php

function DisplayContent()
{
    global $DocBuilder;

    if (!isset($DocBuilder->Configuration["Content"]))
	return ;
    echo "<p>".Translate($DocBuilder->Configuration["Content"])."</p>";
}

function DisplayTable()
{
    global $DocBuilder;

    if (!isset($DocBuilder->Configuration["Table"]))
	return ;
    $array = $DocBuilder->Configuration["Table"];
    if (isset($array[$DocBuilder->Language]))
	$array = $array[$DocBuilder->Language];
    $w = count($array[0]);
?>
    <table class="table">
	<tr>
	    <?php foreach (array_shift($array) as $hd) { ?>
		<th><?=strlen($hd) ? $hd : "&nbsp;"; ?></th>
	    <?php } ?>
	</tr>
	<?php foreach ($array as $ar) { ?>
	    <tr>
		<?php for ($i = 0; $i < $w; ++$i) { ?>
		    <td><?=strlen(@$ar[$i]) ? $ar[$i] : "&nbsp;"; ?></td>
		<?php } ?>
	    </tr>
	<?php } ?>
    </table>
    <?php
}

$Fields = [
    "Table" => "DisplayTable",
    "Content" => "DisplayContent"
];
?>

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

<?php if (isset($DocBuilder->Configuration["TopNotice"])) { ?>
    <p class="top_notice"><?=Translate($DocBuilder->Configuration["TopNotice"]); ?></p>
<?php } ?>

<?php
$Cnt = 0;
$Cnt += isset($DocBuilder->Configuration["Content"]);
$Cnt += isset($DocBuilder->Configuration["Table"]);

if (isset($DocBuilder->Configuration["Order"]))
{
    foreach ($DocBuilder->Configuration["Order"] as $order)
    {
	$Fields[$order]();
	if ($Cnt > 1)
	    echo PageBreak();
    }
}
else
{
    DisplayContent();
    if ($Cnt > 1)
	echo PageBreak();
    DisplayTable();
}


