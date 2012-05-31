<?php
use System\Web\Utils\HTMLUtils;

$appOwner->addClientScript("$.userMessages.init('inbox')");
echo HTMLUtils::beginForm('',false,false,null,array('id'=>'frm-message'));
echo HTMLUtils::renderHiddenField('action', '');
?>
<div class="subnav">
	<ul class="nav nav-pills">
		<li><a href="javascript:void(0)" onclick="$.userMessages.del();"><i class="icon  icon-trash"></i></a>
		</li>
	</ul>
</div>
<table class="table table-bordered" id="message-grid">
	<thead>
		<tr>
			<th width="1"><input type="checkbox" name="chkall" /></th>
			<th>From</th>
			<th>Subject</th>
			<th>Received</th>
		</tr>
	</thead>
	<tbody>
	<?php
	if ($rows) {
		foreach($rows as $k=>$v) {

			$dt = new \CDate(@$v['send']);
			echo '<tr>';
			echo '<td>';
			if (isset($v['read']) && !$v['read']) {
				echo '<i class="icon icon-envelope"></i>';
			}
			echo '<input type="checkbox" name="chk[]" value="'.$k.'">';
			echo '</td>';
			echo '<td><a href="'.URLHelper::add(APP_URL.'user/profile/?id='.$v['from']).'">'.$v['sender'].'</a></td>';
			echo '<td><a href="'.URLHelper::add(APP_URL,'user/messages/read/'.$k).'" data-target="#message-container">'.@$v['subject'].'</a></td>';
			echo '<td>'.\DateUtils::formatUser($dt).'</td>';
			echo '</tr>';
		}
	}
	?>
	</tbody>
</table>
<?php
echo HTMLUtils::endForm(false,false,true);
?>