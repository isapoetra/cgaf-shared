<?php
namespace System\Controllers;
use \UserInfo;
use \System\ACL\ACLHelper;
use System\MVC\Controller;

/**
 * User: Iwan Sapoetra
 * Date: 04/03/12
 * Time: 20:09
 */
class Contacts extends Controller {
  function isAllow($access = 'view') {
    switch ($access) {
      case 'app':
      case 'maincontact':
      case 'view':
        return true;
    }
    return parent::isAllow($access);
  }
  function app() {
    return $this->maincontact($this->getAppOwner()->getConfig('app.maincontact'));
  }
  function maincontact($args = null) {
    /**
     * @var \PersonData $pi
     */
    $pi = null;
    if (is_array($args)) {
      // from rendercontents?
      $pi = isset ($args ['row']) ? $args ['row'] : null;
    } elseif ($args !== null) {
      // from direct access ?
      $m = $this->getModel('person');
      $m->Where('person_id=' . $m->quote($args));
      $pi = $m->loadObject('\\PersonData');
    } else {
      $uid = $args === null ? ACLHelper::getUserId() : $args;
      $uid = $uid == -1 ? $this
        ->getAppOwner()
        ->getConfig('app.maincontact', 1) : $uid;
      $pi = $this
        ->getModel('person')
        ->getPersonByUser($uid);
    }
    if (!($pi instanceof \PersonData)) {
      return '';
    }
    return parent::render(
      'contacts', array(
        'rows' => $pi->getContacts()
      )
    );

  }
}
