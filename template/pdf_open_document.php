<html>
    <head>
	<meta name="charset" content="utf-8" />
	<!--
	---- +-------------------------------------------------------------------------+
	---- |                       HANGED BUNNY STUDIO 2014-<?=date("Y", time()); ?>                     |
	---- +-------------------------------------------------------------------------+
	---- |                           TECHNOCORE DOCUMENT                           |
	---- |                          BLACK BUNNIES  CAVERN                          |
	---- +-------------------------------------------------------------------------+
	---- |                            BY JASON BRILLANTE                           |
	---- +-------------------------------------------------------------------------+
	--->
	<title><?=MustPrint($DocBuilder->Activity, "Activity"); ?></title>
	<meta name="generator" content="Hanged Bunny Studio's Technocore" />
	<?php
	PrintMeta($DocBuilder->Activity, "author", "Author", false);
	PrintMeta($DocBuilder->Activity, "mail", "Mail", false);
	PrintMeta($DocBuilder->Activity, "matter", "Matter", true);
	PrintMeta($DocBuilder->Configuration, "school", "School", true);
	PrintMeta($DocBuilder->Activity, "revision", "Revision", false);
	PrintMeta($DocBuilder->Activity, "activity", "Activity", true);
	PrintMeta($DocBuilder->Instance, "student", "Login", true);
	PrintMeta($DocBuilder->Instance, "limit_date", "DeliveryDate", true);
	PrintMeta($DocBuilder->Instance, "activity_key", "ActivityKey", true);
	?>
	<style>
	 <?php
	 require_once ("./style/default.css");
	 require_once ("./style/colorize.css");
	 require_once ("./style/pdf.css");
	 echo $DocBuilder->Style;
	 ?>
	</style>
    </head>
    <body>
