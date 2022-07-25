<html>
    <head>
	<meta name="charset" content="utf-8" />
	<!--
        ---- +-------------------------------------------------------------------------+
        ---- |                                EFRITS SAS 2008-<?=date("Y", time()); ?>                     |
        ---- |                      PENTACLE TECHNOLOGIE 2008-<?=date("Y", time()); ?>                     |
        ---- |                       HANGED BUNNY STUDIO 2014-<?=2021;              ?>                     |
	---- +-------------------------------------------------------------------------+
	---- |                           TECHNOCORE DOCUMENT                           |
	---- |                          BLACK BUNNIES  CAVERN                          |
	---- +-------------------------------------------------------------------------+
	---- |                      BY JASON BRILLANTE "DAMDOSHI"                      |
	---- +-------------------------------------------------------------------------+
	   --->
	<?php if (isset($DocBuilder->Configuration["Document"])) { ?>
        <title><?=MustPrint($DocBuilder->Configuration, ["Document", $DocBuilder->Language]); ?></title>
	<?php } else if (isset($DocBuilder->Configuration["Title"])) { ?>
        <title><?=MustPrint($DocBuilder->Configuration, ["Title", $DocBuilder->Language]); ?></title>
	<?php } ?>
	<meta name="generator" content="EFRITS, Hanged Bunny Studio && Pentacle Technologie's Technocore" />
	<?php
	$Meta = [
	    "author" => "Author",
	    "mail" => "Mail",
	    "matter" => "Matter",
	    "company_mail" => ["Company", "Mail"],
	    "company" => ["Company", "Name"],
	    "revision" => "Revision",
	    "last_revision" => "LastRevision",
	    "student" => "Login",
	    "limit_date" => "DeliveryDate",
	    "codename" => "CodeName",
	    "generation_date" => "GenerationTime",
	    "token" => "Token"
	];
	if (isset($DocBuilder->Configuration["Login"]))
	    $DocBuilder->Configuration["Login"] = implode(" ", $DocBuilder->Configuration["Login"]);
	foreach ($Meta as $k => $v)
	    PrintMeta($DocBuilder->Configuration, $k, $v, in_array($k, $Types[$DocBuilder->Type]["mandatory"]));
	?>
	<style>
	 <?php
	 require (__DIR__."/../font.css");
	 if (file_exists($cstyle = __DIR__."/../".strtolower($DocBuilder->Pager)."/style.css"))
	     require ($cstyle);
	 if (file_exists($cstyle = __DIR__."/../".strtolower($DocBuilder->Type)."/style.css"))
	     require ($cstyle);
	 ?>
	 <?=$DocBuilder->Style; ?>
	</style>
    </head>
    <body>
	<?php $Page = 1; ?>
