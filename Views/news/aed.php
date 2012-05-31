<?php
use System\MVC\MVCHelper;

use System\Web\Utils\HTMLUtils;
$newstypes = MVCHelper::lookup('news-content-type',\CGAF::APP_ID);
echo HTMLUtils::beginForm('../store');
echo HTMLUtils::renderHiddenField('news_id', @$row->news_id);
if (!$row->app_id) {
	echo HTMLUtils::renderTextBox(__('news.app_id'),'app_id', @$row->app_id,null);
}else{
	echo HTMLUtils::renderHiddenField('app_id', @$row->app_id);
}
if (!$row->controller) {
	echo HTMLUtils::renderTextBox(__('news.controller'),'controller', @$row->controller,null);
}else{
	echo HTMLUtils::renderHiddenField('controller', @$row->controller);
}
echo HTMLUtils::renderTextBox(__('news.item'),'item', @$row->item,null,$row->item ==null);
echo HTMLUtils::renderTextBox(__('news.title'), 'title',@$row->title);
echo HTMLUtils::renderSelect(__('news.type'), 'type',$newstypes,@$row->type,false);
echo HTMLUtils::renderMarkitupEditor(__('news.descr'), 'descr', @$row->descr,array(
		));
echo HTMLUtils::endForm(true,true,true);
?>