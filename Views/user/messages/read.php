<div class="message-actions">
	<div class="btn-group">
		<a class="btn"><i class="icon"><img src="/assets/images/icons/mail-reply-sender.png"/></i>Reply</a>
		<a class="btn"><i class="icon icon-trash"></i>Delete</a>
	</div>
</div>
<div>
	<div><span>From&nbsp;:&nbsp;</span><span><?php echo '<a href="'.URLHelper::add(APP_URL.'user/profile/?id='.$row['from']).'">'.$row['sender'].'</a>'?></span></div>
	<div><span>Subject&nbsp;:&nbsp;</span><span><?php echo $row['subject']?></span></div>
	<hr class="divider"/>
	<div class="message">
		<?php echo $row['contents']?>
	</div>
</div>