<?php

$Alphabet = "abcdefghijklmnopqrstuvwxyz";

function Title($name, array $number)
{
    global $Alphabet;
    
?>
    <h2 index="index<?=$number == [] ? "_summary" : implode("", $number); ?>" class="chapter_title">
	<?php
	if (isset($number[0]))
	{
	    echo ToRoman($number[0]);
	    if (isset($number[1]))
	    {
		echo " - ".$number[1];
		if (isset($number[2]))
		{
		    echo " - ".toupper(substr($Alphabet, $number[2], 1));
		    if (isset($number[3]))
			echo " - ".substr($Alphabet, $number[3], 1);
		}
	    }
	    echo " - ";
	}
	?>
	<?=Translate($name); ?>
    </h2>
<?php
}

