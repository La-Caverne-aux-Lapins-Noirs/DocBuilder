<?php

PrintTitle(["Name" => $Title], $num);

if ($Table)
    WriteTable($Document, $Fields);
else
    foreach ($Fields as $f)
	WriteLabel($Document, $f);


