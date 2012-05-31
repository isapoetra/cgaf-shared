<?php if ($shared) {
	$dirc = $direct ? '-direct' : '';

?>
<div id="pub-share<?php echo $dirc ?>" class="pub-share">
    <div class="title">Share</div>
	<div class="share-content">
		<div>
		<?php
			echo $shared ?>
		</div>
	</div>
</div>
<?php } ?>