<?php
use System\Web\Utils\HTMLUtils;
$rows = isset($rows) ? $rows :array();
echo  $this->getController()->renderActions();
if (!$rows) {
	echo HTMLUtils::renderError('error.nodata');
	return;
}

foreach($rows as $row) {
	echo '<li><a href="'.URLHelper::add(APP_URL,'news/detail/?id='.$row->id).'">'.$row->title.'</a></li>';
}
?>