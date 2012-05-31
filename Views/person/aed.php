<?php
use System\MVC\MVCHelper;

use System\Web\Utils\HTMLUtils;
if ($row && $row instanceof \PersonData) {
	$row->setEditMode(true);
}
if ($errors) {
	echo '<div class="alert">';
	echo '<a class="close" data-dismiss="alert" href="#">&times;</a> <h4 class="alert-heading">Warning!</h4>';
	echo implode('<br/>',$errors);
	echo '</div>';
}
$genders =  MVCHelper::lookup('gender',\CGAF::APP_ID);
echo HTMLUtils::beginForm('../store/',false,false,null,array('id'=>'person-aed'));
if ($row->person_id) {
	echo HTMLUtils::renderHiddenField('id', @$row->person_id);
}

echo '<div class="tabbable tabs-left">';
echo '<ul class="nav nav-tabs">';
echo '  <li class="active"><a href="#1" data-toggle="tab">About</a></li>';
echo '</ul>';
echo '<div class="span12">';
echo '<div class="tab-content" >';
echo '<div class="tab-pane active" id="1">';

echo HTMLUtils::renderTextBox(__('first_name'), 'first_name',@$row->first_name);
echo HTMLUtils::renderTextBox(__('middle_name'), 'middle_name',@$row->middle_name);
echo HTMLUtils::renderTextBox(__('last_name'), 'last_name',@$row->last_name);
echo HTMLUtils::renderSelect(__('gender','Gender'), 'gender',$genders,@$row->gender,'-1','gender');
echo HTMLUtils::renderTextBox(__('employment','Employment'), 'employment',@$row->employment);
echo HTMLUtils::renderDateInput(__ ( "person.birth_date", "Birth Date" ), "birth_date", @$row->birth_date,null,true,null,'datetime' );
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo HTMLUtils::endForm(true,true,true);
if ($row && $row instanceof \PersonData) {
	$row->setEditMode(false);
}