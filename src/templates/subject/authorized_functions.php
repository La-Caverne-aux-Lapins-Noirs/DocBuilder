<?php

if (($def = GetDefinedAuthorisation($Ex["Norm"], "Function", false, "_4columns")) == "")
    $def = "<p style='text-align: center;'>".Translate("None")."</p>";
$doc = str_replace("@@DEFINED_FUNCTIONS", $def, $doc);

if (isset($Ex["AuthorizedFunction"]))
    $doc = str_replace("@@WON_FUNCTIONS", MakeAList(MustBeAnArray($Ex["AuthorizedFunction"]), "", "_4columns"), $doc);
else
    $doc = str_replace("@@WON_FUNCTIONS", "<p style='text-align: center;'>".Translate("None")."</p>", $doc);

echo $doc;

