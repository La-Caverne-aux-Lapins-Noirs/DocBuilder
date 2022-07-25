<?php

require ("01_first_page.php");
echo PageBreak();

require ("02_table_of_contents.php");
echo PageBreak();


$i = 0;
foreach ($DocBuilder->Configuration["Exercises"] as $Ex)
{
    if (isset($Ex["NoDoc"]))
	continue ;
    $DocBuilder->ExercicePage[Translate($Ex["Name"])] = CountPageBreak();
    if ($Ex["Type"] == "Delivery")
	require ("delivery.php");
    else if ($Ex["Type"] == "AuthorizedFunctions")
	require ("authorized_functions_chapter.php");
    else if ($Ex["Type"] == "Function" || $Ex["Type"] == "Program")
	require ("function.php");
    if ($i + 1 < count($DocBuilder->Configuration["Exercises"]))
	echo PageBreak();
    $i += 1;
}
