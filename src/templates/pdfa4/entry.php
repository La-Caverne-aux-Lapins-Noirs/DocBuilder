<?php

PrintTitle(["Name" => $Title], $Num);

if ($Table)
    WriteTable($Document, $Fields);
else
    foreach ($Fields as $f)
	WriteLabel($Document, $f);

