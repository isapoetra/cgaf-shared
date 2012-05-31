<?php
use System\Web\Utils\HTMLUtils;
if (!$rows) {
	if (!$person->isMe()) {
		echo HTMLUtils::renderError(__('person.noactivities'));
	}else{
		echo HTMLUtils::renderError(__('person.noactivitiesme'));
	}
	return;
}
?>