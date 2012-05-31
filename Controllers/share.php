<?php
namespace System\Controllers;
use System\API\PublicApi;

use System\Web\JS\CGAFJS;
use System\Exceptions\UnimplementedException;
use System\Exceptions\SystemException;
use System\MVC\Controller;
use Request;
use CGAF;
class Share extends Controller {
  private $_instances = array ();
  private $_providers = array (
      'config' => array (
          'url' => BASE_URL,
          'description' => '',
          'title' => '',
          'tags' => ''
      ),
      'shareurl' => array (
          'url' => array (
              'Facebook' => array (
                  'shareurl' => 'http://www.facebook.com/sharer.php?u={url}&t={description}',
                  'descr' => 'Share with facebook'
              ),
              'Twitter' => array (
                  'shareurl' => 'http://twitter.com/home?status={description}:{url}'
              ),
              'Delicious' => array (
                  'shareurl' => 'http://del.icio.us/post?url={url}&title={title}&tags:{tags}&notes={description}'
              ),
              'Digg' => array (
                  'shareurl' => 'http://digg.com/submit?phase=2&url={url}&title{title}'
              ),
              'Google' => array (
                  'shareurl' => 'http://www.google.com/bookmarks/mark?op=add&bkmk={url}&title={title}&labels={tags}&annotation={description}'
              ),
              'Yahoo' => array (
                  'shareurl' => 'http://bookmarks.yahoo.com/toolbar/savebm?u={url}&t={title}&d={description}'
              ),
              'Reddit' => array (
                  'shareurl' => 'http://reddit.com/submit?url={url}'
              ),
              'Linkedin' => array (
                  'shareurl' => 'http://www.linkedin.com/shareArticle?mini=true&url={url}&title={title}&source=&summary={description}'
              ),
              'Stumbleupon' => array (
                  'shareurl' => 'http://www.stumbleupon.com/submit?url={url}&title={title}'
              )
          )
      ) /*,'google' => array(
          'config' => array(),
          'plusOne' => array()),
  'facebook' => array(
      'like' => array())*/);
  function __construct($appOwner) {
    parent::__construct ( $appOwner, 'share' );
  }
  function isAllow($access = 'view') {
    switch ($access) {
      case 'view' :
      case 'index' :
      case 'api' :
      case 'community':
      case 'badge':
        return true;
        break;
    }
    return parent::isAllow ( $access );
  }
  private function getInstance($name) {
    if (isset ( $this->_instances [$name] )) {
      return $this->_instances [$name];
    }
    $cname = '\\System\\API\\' . strtolower ( $name );
    $this->_instances [$name] = new $cname ( $this->getAppOwner () );
    return $this->_instances [$name];
  }
  function badge() {
    $appOwner =$this->getAppOwner();
    $badges = array('<a href="https://www.facebook.com/pages/'.$this->getAppOwner()->getConfig('share.badge.facebook.page',$appOwner->getAppId()).'/367579023303880" target="_TOP" style="font-family: &quot;lucida grande&quot;,tahoma,verdana,arial,sans-serif; font-size: 11px; font-variant: normal; font-style: normal; font-weight: normal; color: #3B5998; text-decoration: none;" title="Emanis">Emanis</a><br/><a href="https://www.facebook.com/pages/Emanis/367579023303880" target="_TOP" title="Emanis"><img src="https://badge.facebook.com/badge/367579023303880.2236.1045549944.png" style="border: 0px;" /></a>',
        PublicApi::getInstance('google')->plusOne('tall'),
        PublicApi::getInstance('tweeter')->widget($appOwner->getConfig('share.tweeter.id'))
    );
    $retval = '<div class="share badge">';
    foreach ($badges as $b) {
      $retval .= '<div>'.\Convert::toString($b).'</div>';
    }
    $retval.='</div>';
    return $retval;
  }
  private function share($id = null) {
    $id = $id ? $id : Request::get ( 'id' );
    if (! $id) {
      throw new SystemException ( 'invalid id' );
    }
    $configs ['url'] = Request::get ( 'url' );
    $configs ['description'] = Request::get ( 'description' );
    $configs ['title'] = Request::get ( 'title' );
    foreach ( $configs as $k => $v ) {
      if (! $v) {
        throw new SystemException ( "Invalid Configuration, empty value for %s", $k );
      }
    }
    if (! \Strings::BeginWith ( BASE_URL, $configs ['url'] )) {
      throw new SystemException ( 'sharing external url not allowed' );
    }
    $configs ['tags'] = Request::get ( 'tags', CGAF::getConfig ( 'cgaf.tags' ) );
    $s = $this->_providers [Request::get ( 'service' )] ['url'] [$id] ['shareurl'];
    $url = $v = \Strings::Replace ( $s, $configs, $s, true, null, '{', '}', true );
    // TODO Tracking
    \Response::Redirect ( $url );
  }
  public function community() {
    $retval ='';
    if (CGAF::APP_ID === $this->getAppOwner()->getAppId()) {
      $retval .='<iframe class="item" src="http://githubbadge.appspot.com/badge/isapoetra" style="border: 0;height: 142px;width: 200px;overflow: hidden;" frameBorder=0></iframe>';
      $retval .= '<a class="item" href="http://www.ohloh.net/p/cgaf?ref=WidgetProjectPartnerBadge"><img src="http://www.ohloh.net/p/cgaf/widgets/project_partner_badge.gif"/></a>';
      $retval .= '<a class="item" href ="https://sourceforge.net/projects/cgaf/"><img src="http://sflogo.sourceforge.net/sflogo.php?group_id=150468&amp;type=13" width="120" height="30" border="0" alt="Get CGAF at SourceForge.net. Fast, secure and Free Open Source software downloads"></a>';
    }
    return $retval;

  }
  function Index() {
    if (Request::get ( 'id' )) {
      return $this->share ();
    }
    return parent::Index ();
  }
  function initAction($action, &$params) {
    /*
     * if (!\Request::isDataRequest()) {
    * ppd($this->getAppOwner()->getLiveAsset('share.css')); }
    */
    $action = strtolower ( $action );
    switch ($action) {
      case 'index' :
        //$this->getAppOwner ()->addClientAsset ( CGAFJS::getPluginURL ( 'social-share' ) );
        $owner = $this->getAppOwner ();
        $route = $this->getAppOwner ()->getRoute ();
        $params ['direct'] = $action === 'index' && $route ['_c'] === 'share';
        $share = $this->_providers;
        $content = '';

        $configs ['url'] = Request::get ( 'url', BASE_URL );
        $configs ['description'] = Request::get ( 'description', $owner->getConfig ( 'app.description' ) );
        $configs ['title'] = $owner->getConfig('app.title','Cipta Graha Application Framework' );
        $configs ['tags'] = Request::get ( 'tags', $owner->getConfig ( 'app.tags',$owner->getConfig('app.title') ) );
        foreach ( $configs as $k => $v ) {
          if (! $v) {
            throw new SystemException ( "Invalid Configuration, empty value for %s", $k );
          }
        }
        foreach ( $share as $k => $v ) {
          if ($k === 'config')
            continue;
          $instance = $this->getInstance ( $k );
          $instance->setConfigs ( $configs );
          foreach ( $v as $kk => $vv ) {
            if ($kk === 'config') {
              $instance->setConfigs ( $vv );
            } else {
              $content .= $instance->$kk ( $vv );
            }
          }
          $params ['shared'] = $content;
        }
    }
  }
}
