<?php
use System\Web\Utils\HTMLUtils;

use System\API\PublicApi;

use System\MVC\MVCHelper;

use System\Exceptions\InvalidOperationException;

use System\Models\Person;
use System\ACL\ACLHelper;
use System\Documents\Image;



class PersonData extends \BaseObject {
  public $person_id;
  public $person_owner;
  private $_contacts;
  private $_person;
  private $_privs;
  private $_defaultPrivs;
  private $_cachedPrivs = array();
  private $_currentPerson;
  private $_friends;
  private $_editMode =false;
  private $_infos;
  private $_activities=array();
  function __construct(Person $p) {

    $privs = <<< EOP
{
	"fullname": {
		"privs":16
	},
	"first_name":{
		"privs":16
	},
	"middle_name":{
		"privs":1
	},
	"last_name":{
		"privs":1
	},
	"birth_date":{
		"privs":16,
		"format": "Y"
	},
	"friends" : {
		"privs":2
	},
	"images" : {
		"privs" :2
	}
}
EOP;
    $this->_defaultPrivs = json_decode($privs);
    $this->_person = $p;
  }
  function setEditMode($mode) {
    if ($mode && $this->isMe()) {
      $this->_editMode = $mode;
    }else{
      $this->_editMode = $mode;
    }
  }
  function __set($name, $value) {
    if ($name[1] =='_') {
      return;
    }
    switch ($name) {
      case 'id':
        $name='person_id';
        break;
    }
    if ($this->_editMode) {
      $ivars = array_keys(get_object_vars($this->_person));
      if (in_array($name,$ivars)) {
        return;
      }
    }
    return parent::__set($name, $value);
  }
  function __get($name) {
    $this->loadInfo();
    $val = isset($this->_infos->$name) ? $this->_infos->$name :parent::__get($name);
    if (!$this->canview($name, $val)) {
      return null;
    }
    return $val;
  }

  private function getCurrentPerson() {
    if (!$this->_currentPerson) {
      $cuid = ACLHelper::getUserId();
      $this->_currentPerson = $this->_person->getPersonByUser($cuid);
      if (!$this->_currentPerson) {
        $dummy = new stdClass();
        $dummy->person_id=-1;
        $this->_currentPerson = $dummy;
      }
    }
    return $this->_currentPerson;
  }


  function getStorePath($p = null, $create = false) {
    return \CGAF::getInternalStorage('persons/' . $this->person_id . DS . $p . DS, false, $create);
  }

  private function getCachedImage($f, $size = 'full',$live=false) {
    if ($size === 'full') {
      return $f;
    }
    $fname = $this->getStorePath('.cache/images/', true) . hash('crc32', $f . $size) . \Utils::getFileExt($f);
    @unlink($fname);
    if (!is_file($fname)) {
      $img = new Image($f);
      $out = $img->resize($size, $fname);
    }
    return $fname;
  }

  /**
   * @param string $name
   * @param null $size
   * @return null|string real path if found or null
   */
  function getImage($name = null, $size = null,$live=false) {
    $a = null;

    $f = null;
    if ($name === null) {
      //TODO get from default image configuration
      $name = 'profile/default.png';
    }
    if ($this->canView('images', $a)){
      $f = $this->getStorePath('images') . $name;
    }
    if (!$f || !is_file($f)) {
      $f = CGAF_PATH . 'assets/images/anonymous.png';
    }

    if ($live) {
      //Handled by person controller
      return \URLHelper::add(APP_URL,'person/image/'.basename($name).'?id='.$this->person_id .'&size='.$size);
    }
    return $this->getCachedImage($f, $size,$live);
  }
  function isMe() {
    return $this->person_owner === ACLHelper::getUserId();
  }
  private function isCan($p) {

    $this->getCurrentPerson();
    if ($this->isMe()) {
      return true;
    }
    $ok = false;
    $isfriend = $this->isFriend();
    $isfriendOf = $this->isFriendOf();

    if ((PersonACL::PUBLIC_ACCESS & $p) === PersonACL::PUBLIC_ACCESS) {
      $ok = true;
    } elseif (((PersonACL::PRIVATE_ACCESS & $p) === PersonACL::PRIVATE_ACCESS) && $this->isMe()) {
      $ok = true;
    } elseif ($isfriend && (((PersonACL::FRIEND_ACCESS & $p) === PersonACL::FRIEND_ACCESS))) {
      $ok = true;
    } elseif ($isfriendOf && (((PersonACL::FOF_ACCESS & $p) === PersonACL::FOF_ACCESS))) {
      $ok = true;
    }
    return $ok;
  }

  function canView($var, &$val) {
    $var = strtolower($var);
    $v = $this->getPersonPrivs();
    if (!$v) ppd($var);
    if (!isset ($v->$var)) {

      $v->$var = new stdClass();
      $v->$var->privs = PersonACL::PRIVATE_ACCESS;
    }
    if (isset ($this->_cachedPrivs [$var])) {
      return $this->_cachedPrivs [$var];
    }
    $isOther = $this->isMe() === false;
    $p = ( int )($v->{$var}->privs ? $v->{$var}->privs : PersonACL::PRIVATE_ACCESS);
    $ok = $this->isCan($p);
    if ($ok) {
      switch ($var) {
        case 'gender':
          if ($val !==null) {
            $lookup = MVCHelper::lookup('gender',\CGAF::APP_ID);
            $val= isset($lookup[$val]) ? $lookup[$val] : __('gender.unknown','Unknown');
          }
          break;
        case 'birth_date' :
          if ($val) {
            if ($this->_editMode) return $val;
            $d = new \CDate ($val);
            $format =$this->isMe() ? $this->_person->getAppOwner()->getUserConfig('date.clientformat','m/d/Y') : (isset ($v->{$var}->format) ? $v->{$var}->format : 'Y');
            $val = $d->format($format) . ',<span>' . $d->diff(new \CDate())->format('%y Years') . '</span>';
          }
          break;
      }
    }
    $this->_cachedPrivs [$var] = $ok;
    return $this->_cachedPrivs [$var];
  }

  private function getPersonPrivs() {
    if (!$this->_privs) {
      if ($this->person_owner < 0) {
        $this->_privs = $this->_defaultPrivs;
      } else {
        $f = \CGAF::getUserStorage($this->person_owner, false) . $this->person_id . DS . 'privs.json';
        if (is_file($f)) {
          $this->_privs = json_decode(file_get_contents($f, false));
        }else{
          $this->_privs = $this->_defaultPrivs;
        }
      }
    }
    return $this->_privs;
  }

  public function getFullName() {
    return sprintf('%s %s %s', $this->first_name, $this->middle_name, $this->last_name);
  }

  function getFriends() {
    if ($this->_friends === null) {
      $this->_friends = array();
      $r = null;
      if ($this->canview('friends', $r)) {
        $f = \CGAF::getUserStorage($this->person_owner, false) . $this->person_id . DS . 'friends.json';
        if (is_file($f)) {

          ppd($f);
        }
      }
    }
    return $this->_friends;
  }

  function isFriend($uid = null) {
    $uid = $uid !== null ? $uid : ACLHelper::getUserId();
    if ($uid === $this->person_owner) {
      return true;
    }
    if ($uid === -1) {
      return false;
    }
    $friends = $this->getFriends();


  }

  function isFriendOf($uid = null) {
    $uid = $uid !== null ? $uid : ACLHelper::getUserId();
    return true;
  }
  private function loadActivities($day=null) {
    //Storage format
    // year/month/date....
    if ($day ===null) {
      $day = CDate::Current('Y/m/');
    }
    if (!isset($this->_activities[$day])) {
      $last = $this->getStorePath().'last.activities';
      if (is_file($last)) {
        ppd($last);
      }

      $p = $this->getStorePath('activities/'.$day);
      if (is_dir($p)) {
        $files = \Utils::getDirList($p);
        ppd($p);
      }
    }

  }
  function getActivities() {
    $val = null;
    $this->loadActivities();
    if ($this->person_id !== ACLHelper::getUserId() && !$this->canView('activities', $val)) {
      return null;
    }
    return $this->_activities;
  }

  function getContacts() {
    if ($this->_contacts === null) {
      $f = \CGAF::getUserStorage($this->person_owner, false) . $this->person_id . DS . 'contacts.json';

      if (is_file($f)) {
        $this->_contacts = array();
        $c = json_decode(file_get_contents($f));
        $isprivate = true;
        $isfriend = true;
        $cuid = ACLHelper::getUserId();
        $cperson = $this->getCurrentPerson();
        $isfriendOf = true;
        $ispublic = $cuid === -1;
        if (( int )$cperson->person_id !== $this->person_id) {
          $isfriend = $this->isFriend($cuid);
          $isfriendOf = $this->isFriendOf($cuid);
        }
        foreach ($c as $v) {
          $v->privs = ( int )($v->privs ? $v->privs : PersonACL::PRIVATE_ACCESS);
          if ($this->isCan($v->privs)) {
            $this->_contacts [] = $v;
          }
        }
      } else {
        $this->_contacts = false;
      }
    }
    return $this->_contacts;
  }
  function assign($var, $val = null) {
    if (!$this->_editMode) {
      $this->_internal =array();
    }

    parent::assign($var,$val);
  }
  private function loadInfo($force =false) {
    if ($force || !$this->_infos) {
      $fstore = $this->getStorePath().'infos.json';
      if (is_file($fstore)) {
        $this->_infos =  json_decode(file_get_contents($fstore));
      }else{
        $this->_infos =  new \stdClass();
      }
    }
    return $this->_infos;
  }
  public function store() {
    $ivars = array_keys(get_object_vars($this->_person));
    $old = $this->loadInfo();
    $changed = false;
    foreach ($this->_internal as $k=>$v) {
      if (! in_array($k,$ivars)) {
        if ($old->$k !== $v) {
          $old->$k = $v;
          $changed=true;
        }
      }
    }
    if ($changed) {
      $fstore = $this->getStorePath().'infos.json';
      file_put_contents($fstore, json_encode($old));
    }
    $this->loadInfo(true);
    $this->_editMode =false;
  }
  public static function getPrimaryCurrentUser() {
    $m= AppManager::getInstance()->getModel('person');
    $o = $m->getPersonByUser(ACLHelper::getUserId());
    return $o;
  }
  public static function parseContact($row) {
    // TODO parse by application
    switch ($row->type) {


      case 'email' :
        $retval = '<a href="mailto:' . $descr . '" class="email"><img src="' . ASSET_URL . '/images/email.png"/><span>' . __('sendemail') . '</span></a>';
        break;
      case 'skype' :
        $retval = PublicApi::share('skype', 'onlinestatus', $descr);
        break;
      case 'ymsgrstatus' :
        $retval = PublicApi::share('yahoo', 'onlinestatus', $descr);
        break;
      default :
        try {
          $retval = PublicApi::share($row->api, $row->type,$row->configs);
        }catch(\Exception $e) {
          if (CGAF_DEBUG) {
            $retval = HTMLUtils::renderError($e->getMessage());
          }
        }
        break;
    }
    if ($retval) {
      $retval = '<div class="contact ' . $row->type . '">' . $retval . '</div>';
    }
    return $retval;
  }
  public static function getInfo($id) {
    //TODO Cache
    $m= AppManager::getInstance()->getModel('person')->clear();
    $m->where('person_id='.$m->quote($id));
    return $m->loadObject('\\PersonData');
  }
}
