<?php
$date = DateUtils::formatUser($row->date_created,true);
$right = $this->getController()->renderContent('right');
?>
<div class="news-detail">
	<div class="<?php $right ? 'span10' : 'span13'?>">
		<div>
			<div class="date">
				<?php echo $date?>
			</div>
			<div class="title">
				<?php echo $row->title?>
			</div>
			<div class="short-descr">
				<?php echo $row->short_descr?>
			</div>
			<div class="author">
				<?php echo __('by','By').': '.$row->fullname?>
			</div>
		</div>
		<hr class="divider" />
		<div class="detail">
			<?php echo $row->contents;?>
		</div>
	</div>
	<?php if ($right) {?>
	<div class="span2">
		<div><?php echo $right;?></div>
	</div>
	<?php }?>
</div>
