<p class="function_introduce">
    <?=Translate("WriteThatFunction"); ?>:
</p>
<div class="code">
    <?php
    if (is_array($Configuration["Prototype"])) {
	foreach ($Configuration["Prototype"] as $func) {
	    echo PrettyCode([], $func)."<br />";
	}
    } else {
	echo PrettyCode([], $Configuration["Prototype"]);
    }
    ?>
</div>
<div class="function_description markdown">
    <?php
    $all = "";
    if (is_array($Configuration["Description"]))
    {
	foreach ($Configuration["Description"] as $d)
	{
	    $all .= Translate($d);
	}
    }
    else
	$all = Translate($Configuration["Description"]);
    $all = explode("\n", $all);
    foreach ($all as $i => $j)
    {
	$all[$i] = trim($j);
    }
    $all = implode("\n", $all);
    echo $MarkDown->text($all);
    ?>
</div>
