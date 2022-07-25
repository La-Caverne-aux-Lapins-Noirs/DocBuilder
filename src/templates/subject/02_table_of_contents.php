<h2><?=MustPrint($DocBuilder->Dictionnary, "Index"); ?></h2>
<div class="top_block"></div>
<table class="table_of_content">
    <?php BrowseExercises($DocBuilder->Configuration["Exercises"], "PrintTableContentEntry"); ?>
</table>

