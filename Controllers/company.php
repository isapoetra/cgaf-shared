<?php
namespace System\Controllers;
use System\Web\UI\Controls\Anchor;

use System\Exceptions\InvalidOperationException;
use System\Exceptions\SystemException;
use System\MVC\Controller;
//TODO Work as Company Information System
class CompanyController extends Controller {
  function isAllow($access = 'view') {
    switch ($access) {
      case 'view':
      case 'index':
      case 'pick':
      case 'profile':
      case 'images':
        return $this->isFromHome();
        break;
      default:
        break;
    }
    return parent::isAllow($access);
  }
  function images() {
    $image = $this->getAppOwner()->getAsset('company-no-logo.png');
    $url = explode('/',$_REQUEST['__url']);
    array_shift($url);
    array_shift($url);
    $cid = $this->getModel()->clear()->load($url[0]);
    $fname = basename($url[1]);
    if ($cid) {
      $f = $this->getInternalPath('images/'.$url[0].'/',false).$fname;
      if (!is_file($f)) {
        $f = \CGAF::getInternalStorage('company/'.$url[0].'/images/').$fname;
        if (is_file($f)) {
          $image =$f;
        }
      }else{
        $image = $f;
      }
    }
    //TODO Cache & Resize
    return \Streamer::Stream($image);
  }
  function pick() {
    return $this->index();
  }
  function index() {
    if (\Request::isDataRequest()) {
      $rows =  $this->getModel()->reset(__FUNCTION__)->loadObjects(null,\Request::get('_cp'),\Request::get('_rpp'));
      foreach($rows as $row) {
        $row->logo = \URLHelper::add(APP_URL,'company/images/'.$row->company_id.'/logo.png');
        $row->company_name = Anchor::link(\URLHelper::add(APP_URL,'company/profile/?id='.$row->company_id),$row->company_name);
      }
      return $rows;
    }
    return parent::render(__FUNCTION__,array(
        'title'=>'Pick Company',
        'rowCount' => $this->getModel()->reset(__FUNCTION__)->clear('field')->select('count(*)','c',true)->loadObject()->c,
        'columns'=>array(
            array(
                'title'=>'company_name',
                'field'=>'company_name'
            ),
            array(
                'title'=>'&nbsp;',
                'field'=>'actions')
        )));
  }
  function register() {
    if ($this->getAppOwner()->isValidToken()) {

    }
    return parent::render(__FUNCTION__,array(
    ));
  }
  function Initialize() {
    if (parent::Initialize()) {
      $this->setModel('companies');
      return true;
    }
    return false;
  }

  function profile() {
    $row = $this->getModel()->reset()->load(\Request::get('id'));
    if (!$row) {
      throw new InvalidOperationException('Invalid ID');
    }
    $this->getAppOwner()->assign('title', $row->company_name);
    return parent::renderView(__FUNCTION__, array(
        'mode' => \Request::get('mode', 'full'),
        'row' => $row));
  }
}
