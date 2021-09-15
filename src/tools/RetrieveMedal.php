 <?php

function RetrieveMedal($name)
{
    global $DocBuilder;

    $medal = [];
    $target = $DocBuilder->MedalsDir."/".$name;
    if (!($file = json_decode(shell_exec("mergeconf -i $target.dab -of .json 2> /dev/null"), true)))
    {
	if (!file_exists($target.".png"))
	    shell_exec("genicon '$name' > $target.png");
	$medal["CodeName"] = $name;
	$medal["Name"] = $name;
	$medal["Icon"] = "$name.png";
	$medal["Description"] = sprintf(Translate("FunctionSDone"), $name);
	$medal["Type"] = "band";
	$json = json_encode($medal, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	shell_exec("echo '$json' | mergeconf -if .json -o $target.dab");
	$medal["Icon"] = $DocBuilder->MedalsDir."/$name.png";
    }
    else
    {
	$medal["CodeName"] = $name;
	$medal["Name"] = Translate(@$file["Name"]);
	$medal["Description"] = Translate(@$file["Description"]);
	$medal["Icon"] = isset($file["Icon"]) ? $file["Icon"] : "";

	if (substr($medal["Icon"], 0, 1) != "/")
	    $medal["Icon"] = $DocBuilder->MedalsDir."/".$medal["Icon"];

	if (!isset($file["Type"]))
	    $medal["Type"] = "coin";
	else
	    $medal["Type"] = $file["Type"];
    }
    return ($medal);
}
