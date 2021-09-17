
<h2><?=MustPrint($DocBuilder->Dictionnary, "Index"); ?></h2>
<div id="table_of_content">
    <?php
    $Depth = [];
    BrowseExercises($Ex, $Depth, "PrintTitle");
    ?>
</div>
