<?php
namespace System\Controllers;
use System\ACL\ACLHelper;

use System\MVC\Controller;
class TodoController extends Controller {
	function isAllow($access = 'view') {
		return ACLHelper::isInrole(ACLHelper::DEV_GROUP);
	}
	function simple() {
		$m = $this->getModel();
		$rows = $m->loadObjects();
		return parent::renderView(__FUNCTION__, array(
				'rows' => $rows));
	}
	function Index() {
		$m = $this->getModel();
		$rows = $m->loadObjects();
		return parent::renderView(__FUNCTION__, array(
				'rows' => $rows));
	}
}
