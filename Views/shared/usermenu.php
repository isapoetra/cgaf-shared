<?php
use System\Web\UI\Items\MenuItem;

use \System\Web\Utils\HTMLUtils;
use System\Web\UI\Controls\Menu;
if (!\CGAF::isInstalled()) {
	return;
}
$menu = new Menu();

$id = \System\ACL\ACLHelper::getUserId();
$appOwner = $this->getAppOwner();
$items = $appOwner->getMenuItems('user-menu', 0, null, true, true);
$menu->addChild($items);
$menu->setId('user_menu');
$menu->setClass('nav nav-pills user-menu');
//$menu->addStyle('float','right');
$replacer = array();

if ($appOwner->isAuthentificated()) {
	$auth = $appOwner->getAuthInfo();
	$pi = $appOwner->getModel('person')
	->getPersonByUser($auth->getUserId());
	if ($pi && $pi->person_id) {
		$replacer['FullName'] = $pi->getFullName();
	} else {
		$pi = $auth->getUserInfo();
		$replacer['FullName'] = $pi->user_name;
	}
	if (!$replacer['FullName']) {
		$replacer['FullName'] ='My';
	}
	$menu->setReplacer($replacer);
	$uc = $this->getController('user');
	$msgs = $uc->getMyMessages();
	if ($msgs->unread >0) {
		$menu->add(new MenuItem(array(
				'attrs'=> array('class'=>'user-notification'),
				'caption'=>$msgs->unread.'/'.$msgs->count,
				'menu_icon'=>ASSET_URL.'images/icons/mail-unread.png',
				'menu_action'=>URLHelper::add(APP_URL,'user/messages')
		)));
	}
} else {
	$replacer['FullName'] = '';
}

echo '<div class="nav-collapse" id="user-menu-container">';
echo $menu->render(true);
echo '</div>';
return;
?>