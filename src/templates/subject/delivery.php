<?php
require ("title.php");
require ("concerned_files.php");
echo Translate($Ex["Document"]);
echo GetDefinedAuthorisation($Ex["Norm"], "Keywords");
require ("medal_list.php");
