<?php
require ("title.php");
require ("concerned_files.php");
echo Translate($Ex["Document"]);
echo "<br /><br />";

/////////////////////////////////////////////////
// Spécifictés d'autorisation sur cet exercice //
/////////////////////////////////////////////////

// Spécificités d'autorisation et d'interdictions de fonctions
$doc = "@@DEFINED_FUNCTIONS";
require ("authorized_functions.php");

// Spécificités d'autorisation et d'interdictions de style
if (@$Ex["Norm"]["Describe"]) { ?>
    <br />
    <p style="font-weight: bold;"><?=Translate("NormException"); ?></p>
    <?php foreach (display_norm($Ex["Norm"], false) as $r) { ?>
	<?=$r; ?>
    <?php } ?>
<?php
}

/////////////////
// Récompenses //
/////////////////

require ("medal_list.php");
