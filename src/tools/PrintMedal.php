<?php
function PrintMedal($m)
{
    global $DocBuilder;

    if (!@strlen($medal_dir = $DocBuilder->Configuration["MedalDir"]))
	$medal_dir = "/opt/technocore/medal/";
    if (!@strlen($genicon = $DocBuilder->Configuration["GenIcon"]))
	$genicon = "/opt/technocore/genicon.dab";
    if (file_exists($medal_dir."/".$m.".png"))
    {
	$picture = $medal_dir."/".$m.".png";
	$picture = file_get_contents($picture);
	$text = LoadDabsic($medal_dir."/".$m.".dab");
    }
    else
    {
	$picture = XShellExec("genicon sband $m -c $genicon");
	$text = [
	    "Type" => "Band",
	    "Name" => ucfirst($m)
	];
    }
?>
<div class="medal_picture" style="background-image: url(<?=Base64Picture($picture, false); ?>); <?=$text["Type"] == "Band" ? "border-radius: 50%; border: 1px solid black;" : ""; ?>"></div>
<div class="medal_description">
    <h5><?=Translate(@$text["Name"]); ?></h5>
    <p><?=Translate(@$text["Description"]); ?></p>
</div>
<?php
}
