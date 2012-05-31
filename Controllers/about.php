<?php
namespace System\Controllers;
use System\API\PublicApi;
use System\Exceptions\SystemException;
use System\JSON\JSONResult;
use System\ACL\ACLHelper;
use \Request;
use System\MVC\MVCHelper;
use System\MVC\StaticContentController;
use System\Template\TemplateHelper;
class AboutController extends StaticContentController {
  function isAllow($access = 'view') {
    switch (strtolower($access)) {
      case 'view':
      case 'index':
      case 'cgii':
      case 'app':
      case 'cgaf':
      case 'auth':
      case 'donnatebutton':
      case 'donnate':
      case 'assets':
        return true;
        break;
    }
    return parent::isAllow($access);
  }

  protected function getParamForAction($a, $mode = null) {
    $retval = parent::getParamForAction($a, $mode);
    switch (strtolower($a)) {
      case 'auth':
        $retval['joinurl'] = \URLHelper::add(APP_URL, 'user/register');
    }
    return $retval;
  }
  function assets() {
    $ori = MVCHelper::getUrl();
    $app = array_shift($ori);
    $instance = \AppManager::getInstanceByPath($app,false);
    if (!$instance) {
      $instance = $this->getAppOwner();
    }
    $fname = basename(array_pop($ori));
    $spath =array();
    $spath[] = $instance->getInternalStorage('contents/about/');
    $spath[] = \CGAF::getInternalStorage('contents/about/app/'.$app.'/',false);

    $ori = ACLHelper::secureFile(implode('/',$ori),true).DS;
    foreach($spath as $f) {
      $fs = $f.$ori.$fname;
      if (is_file($fs)) {
        return \Streamer::Stream($fs);
      }
    }
    \CGAF::doExit();
  }
  function app() {
    $ori = MVCHelper::getUrl();
    if (count($ori)) {
      $app = $ori[0];
    }

    if (\Request::get('appid')) {
      $app = \Request::get('appid');
    }elseif (!$app) {
      $app= $this->getAppOwner()->getAppName();
    }
    $app = strtolower($app);
    $spath =array();

    $instance = \AppManager::getInstanceByPath($app,false);
    if (!$instance) {
      $instance = $this->getAppOwner();
    }
    $fidx = \Utils::getFileName(ACLHelper::secureFile(\Request::get('f', 'index'), false), false);
    $spath[] = $instance->getInternalStorage('contents/about/');
    $spath[] = \CGAF::getInternalStorage('contents/about/app/'.$app.'/',false);
    $params = array('baseurl'=>BASE_URL,
        'asseturl'=>BASE_URL.'about/assets/'.$app.'/');
    foreach($spath as $path) {
      $f = $path.DS.$fidx;
      $lc = $instance->getLocale()->getLocale();
      $alt = $f . '-' . $lc . '.html';
      if (is_file($alt)) {
        $f = $alt;
      } else {
        $f = $f . '.html';
      }
      if (is_file($f)) {
        return $this->renderFile(__FUNCTION__, $f,$params);
      }

    }
    return  __('app.about.notfound');
  }
  function auth() {
    $this->_template = null;
    return parent::Index(__FUNCTION__);
  }
  function edit($row = null) {
    $a = ACLHelper::secureFile(\Request::get('id'), false);
    $f = $this->getContentFile($a, false);
    if (!is_file($f)) {
      file_put_contents($f, '');
    }
    if (!$f) {
      throw new SystemException('error.invalidcontent');
    }
    if ($this->getAppOwner()->isValidToken()) {
      $fcontent = Request::get('fcontent', null, false);
      file_put_contents($f, $fcontent);
      return new JSONResult(0, __('message.datasaved'));
    }
    $params = $this->getParamForAction($a, __FUNCTION__);
    $cl = $this->getAppOwner()->getLocale()->getLocale();
    $dl = $this->getAppOwner()->getLocale()->getDefaultLocale();
    return $this
    ->render('shared/editor',
        array(
            'params' => $params,
            'title' => 'Edit data for About: ' . __('about.' . $a) . ' <a href="' . BASE_URL . 'about/' . $a . '" target="__aboutPreview">preview</a>',
            'subtitle' => 'Editing data for language :' . __('locale.' . $cl) . ($dl == $cl ? ' [Default]' : ''),
            'file' => $f));
  }
  function renderAbout($what, $sub) {
    $abpath = $this->getInternalPath($what);
    return parent::renderFile($what, $abpath . $sub);
  }
  function donnatebutton() {
    $donnateProfider = $this->getConfig('donnate.providers', array(
        'paypal',
        'google'));
    $retval = '';
    foreach ($donnateProfider as $d) {
      $api = PublicApi::getInstance($d);
      if (method_exists($api, 'donnate')) {
        $retval .= $api->donnate();
      }
    }
    return $retval;
  }
  function Index($a = null) {
    $route = MVCHelper::getRoute();
    $a = $route['_a'];
    switch ($a) {
      case 'index':
        return parent::renderMenu('about-company');
    }
    return parent::Index();
  }
}
