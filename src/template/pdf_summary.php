<ul class="index">
    <?php foreach (Summary() as $i => $act) { ?>
	<li class="index_entry">
	    <a href="#index<?=$i + 1; ?>">
		<div>
		    <div class="index_roman">
			<?=ToRoman($i + 1); ?>
		    </div>
		    <div class="index_chapter">
			<?=Translate($act); ?>
		    </div>
		    <div class="index_page">
			!@@@<?=Translate($act); ?>@@@!
		    </div>
		</div>
	    </a>
	</li>
    <?php } ?>
</ul>

