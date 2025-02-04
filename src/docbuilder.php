<?php
foreach (glob(__DIR__."/*/*.php") as $file)
    require_once ($file);

$Configuration = NULL;

function main($argc, array $argv)
{
    global $Configuration;
    
    if ($argc == 1)
	return (Usage());

    $Configuration = BuildConfiguration($argc, $argv);

    if (!isset($Configuration["Document"]))
	die("Missing document type.\n");
    $Configuration["Document"] = strtolower($Configuration["Document"]);
    $Configuration[".Directory"] = "src/documents/{$Configuration["Document"]}/";
    if (!is_dir($Configuration[".Directory"]))
	die("Invalid document type {$Configuration["Document"]}.\n");
    foreach (glob($Configuration[".Directory"]."*.php") as $file)
	require_once ($file);

    ob_start("KeepContent");
    BuildDocument($Configuration);
    $Configuration[".Engine"] = "src/engines/{$Configuration[".Engine"]}/";
    if (!is_dir($Configuration[".Engine"]))
	die("Invalid engine {$Configuration[".Engine"]} for document type {$Configuration["Document"]}.\n");
    foreach (glob($Configuration[".Engine"]."*.php") as $file)
	require_once ($file);
    
    $str = ob_get_clean();
    $str = explode("\n", $str);
    foreach ($str as &$line)
	$line = trim($line);
    $str = implode("\n", $str);

    
    if (($str = ResolveDirectives($Configuration, $str, "[@")) == NULL)
	return (1);
    if (($str = ResolveDirectives($Configuration, $str, "[#")) == NULL)
	return (1);
    if ($Configuration[".Debug"])
	echo $str;
    Compile($Configuration, $str);
    return (0);
}

exit (main(count($argv), $argv));

