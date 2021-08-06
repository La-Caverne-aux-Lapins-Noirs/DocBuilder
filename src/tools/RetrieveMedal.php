<?php

function RetrieveMedal($name)
{
    global $DocBuilder;

    $medal = [];
    $target = $DocBuilder->MedalsDir."/".$name;
    if (!($file = json_decode(shell_exec("mergeconf -i $target.dab -of .json 2> /dev/null"), true)))
    {
	shell_exec("rm -f $target.png && genicon '$name' > $target.png"); // GenIcon doit être installé.
	$medal["Name"] = $name;
	$medal["Icon"] = "$target.png";
	$medal["Description"] = sprintf(Translate("FunctionSDone"), $name);
	$medal["Type"] = "band";
	$json = json_encode($medal, JSON_UNESCAPED_SLASHES);
	shell_exec("echo '$json' | mergeconf -if .json -o $target.dab");
	}
    else
    {
	$medal["Name"] = Translate($file["Name"]);
	$medal["Icon"] = $file["Icon"];
	$medal["Description"] = Translate($file["Description"]);
	if (!isset($file["Type"]))
	    $medal["Type"] = "coin";
	else
	    $medal["Type"] = $file["Type"];
    }
    return ($medal);
}
