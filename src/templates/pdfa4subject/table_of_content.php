<?php StartSubRecord(); ?>
<h2><?=MustPrint($DocBuilder->Dictionnary, "Index"); ?></h2>
<div id="table_of_content">
    <?php
    $Depth = [];
    BrowseExercises($DocBuilder->Activity["Exercises"], $Depth, "PrintTableContentEntry");
    ?>
</div>
<?php StopSubRecord();

