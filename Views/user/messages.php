<?php
$mUrl = URLHelper::add(BASE_URL,'user/messages/');
$flags = array(
		'inbox',
		'outbox',
		'trash'
);
?>
<div class="container-fluid">
	<div class="row-fluid">
		<div class="span2">
			<ul class="nav nav-list">
				<?php
					foreach($flags as $f) {
						echo '<li '.($f===$flag ? 'class="active"' : '').'>';
						echo '<a href="'.$mUrl.$f.'" data-target="#message-container" onclick="$(this).closest(\'ul\').find(\'li\').removeClass(\'active\');$(this).parent().addClass(\'active\');">';
						echo __('users.message.flag.'.$f,ucwords($f)).' ('. count($messages->$f->unread()).'/'.$messages->$f->count() .')';
						echo '</a></li>';
					}
				?>
			</ul>
		</div>
		<div class="span10" id="message-container">
			<?php echo $this->getController()->renderMessageByFlag('inbox')?>
		</div>
	</div>
</div>
