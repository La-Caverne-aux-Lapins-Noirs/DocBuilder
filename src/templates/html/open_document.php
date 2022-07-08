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
	<title><?=MustPrint($DocBuilder->Configuration, "Title"); ?></title>
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
	    "activity" => "Configuration",
	    "student" => "Login",
	    "limit_date" => "DeliveryDate",
	    "codename" => "CodeName",
	    "generation_date" => "GenerationTime"
	];
	foreach ($Meta as $k => $v)
	    PrintMeta($DocBuilder->Configuration, $k, $v, in_array($k, $Types[$DocBuilder->Type]["mandatory"]));
	?>
	<style>
	 <?php
	 if (file_exists($cstyle = __DIR__."/../".strtolower($DocBuilder->Pager)."/style.css"))
	     require ($cstyle);
	 ?>
	 <?=$DocBuilder->Style; ?>
	</style>
    </head>
    <body>
