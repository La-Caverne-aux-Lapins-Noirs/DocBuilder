<?php

if (($def = GetDefinedAuthorisation($Ex, "Function", true, "_8columns function_list", $chapter ? "" : "Add")) == "")
    $def = "<p style='text-align: center;'>".Translate("None")."</p>";
$doc = str_replace("@@DEFINED_FUNCTIONS", $def, $doc);

if (@$Ex["WonFunction"])
{
    if (isset($DocBuilder->Configuration["AuthorizedFunction"]))
	$doc = str_replace("@@WON_FUNCTIONS", MakeAList(MustBeAnArray($DocBuilder->Configuration["AuthorizedFunction"]), Translate("WonFunction"), "_8columns function_list"), $doc);
    else
	$doc = str_replace("@@WON_FUNCTIONS", "<p style='text-align: center;'>".Translate("None")."</p>", $doc);
}
else
    $doc = str_replace("@@WON_FUNCTIONS", "", $doc);

echo $doc;
$doc = "";
