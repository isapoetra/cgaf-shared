<?php
use System\JSON\JSON;
?>

<div><h3><?php echo $title?></h3></div>
<a class="btn btn-primary" href="/company/register/">Register New Company</a>
<div id="events-grid"></div>
<?php
$dataUrl = isset($dataUrl) ? $dataUrl : URLHelper::add(APP_URL,$appOwner->getRoute('_c').'/'.$appOwner->getRoute('_a').'/?__data=json');
$columns = JSON::encodeConfig($columns);
$js = <<< EOT
$('#events-grid').grid({
	rowPerPage : 10,
	rowCount:$rowCount,
	dataUrl: '$dataUrl',
	columns : $columns,
	pagination: {
		currentPage : 0,
		visible : true
	}
});
EOT;
$appOwner->addClientScript($js);
?>