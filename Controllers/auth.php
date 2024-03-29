<?php
namespace System\Controllers;
use System\JSON\JSONResponse;

use System\JSON\JSONResult;
use System\Exceptions\SystemException;
use System\Exceptions\UnimplementedException;
use System\Auth\OpenId;
use System\Auth\Auth;
use System\Web\JS\JSUtils;
use System\MVC\Controller;
use Request;

class AuthController extends Controller {
  public function isAllow($access = "view") {
    $isAuth = $this->getAppOwner()->isAuthentificated();
    switch (strtolower($access)) {
      case 'browserid':
      case 'external' :
        // damn recursive on facebook
        return $this->getAppOwner()->getConfig('auth.External.enabled', true);
      case 'index' :
      case 'view' :
        return true;
      case 'login' :
        return \CGAF::isInstalled() && $isAuth === false;
      case 'logout' :
        return \CGAF::isInstalled() && $isAuth === true;
    }
    return parent::isAllow($access);
  }
  function getAction($o, $id = null, $route = null,$params=null) {
  }
  private function post_request($url, $data) {
    $header ='Content-type:application/x-www-form-urlencode';
    $params = array(
        'http' => array(
            'header'=>$header,
            'method' => 'POST',
            'content' => $data));
    // workaround for php bug where http headers don't get sent in php 5.2
    if(version_compare(PHP_VERSION, '5.3.0') == -1){
      ini_set('user_agent', 'PHP-SOAP/' . PHP_VERSION . "\r\n" . $header);
    }
    $ctx = stream_context_create($params);
    $fp = fopen($url, 'rb', false, $ctx);
    if ($fp) {
      return stream_get_contents($fp);
    }else {
      return FALSE;
    }
  }
  function browserId() {
    $token =\Request::get('assertion');
    $parameters = http_build_query(array('assertion' => $token, 'audience' => $_SERVER['HTTP_HOST']));
    $result = json_decode($this->post_request('https://browserid.org/verify', $parameters), TRUE, 2);
    if(isset($result['status']) && $result['status'] == 'okay')
    {
      ppd($result);
      $this->email = $result['email'];
      $this->validity = $result['valid-until'];
      $this->issuer = $result['issuer'];
      return true;
    }
    else
    {
      return false;
    }

    pp($token);
    ppd($_REQUEST);
  }
  function external() {
    $p = Request::get('id');
    $mode = Request::get('mode');
    if (\Request::get('popupmode')) {
      \Request::isAJAXRequest(true);
      \Response::clearBuffer();
    }
    $ajax = Request::isAJAXRequest() ? '__ajax=1&' : '';
    $provider = Auth::getProviderInstance($p);
    if ($this->getAppOwner()->isAuthentificated()) {
      return \Response::Redirect(\URLHelper::add(APP_URL,'/user/profile/'));
    }

    if (!$provider) {
      throw new SystemException ("unhandled authentification method");
    }
    $rurl = \URLHelper::add(APP_URL, 'auth/external/', $ajax . 'id=' . $p);
    $state = $provider->getState();
    $params = array(
        'message'=>null,
        'lastError' => $provider->getLastError(),
        'providername' => $p,
        'provider' => $provider,
        'remoteuser' => $provider->getRemoteUser(),
        'localuser' => $provider->getRealUser(),
        'authurl' => $rurl
    );
    if ($mode) {
      switch ($mode) {
        case 'reset':
          $provider->reset();
          $this->redirect('external',array('id'=>$p));
          break;
        case 'rtl' : // register to local
          if ($this->getAppOwner()->isValidToken()) {
            \Utils::bindToObject($params ['remoteuser'], \Request::gets('p'),true);
          }
          if (!$params ['localuser'] && $params ['remoteuser']) {
            $u = $this->getController('user');
            try {
              $valid = $u->registerDirect($params ['remoteuser'], $p);
              if (is_array($valid) && !$valid ['result']) {
                $params ['message'] = $valid ['message'];
                return $this->renderView('external/register', $params);
              }
            } catch (\Exception $e) {
              $params ['message']['__common'] = $e->getMessage();
              \Request::isAJAXRequest(false);
              return $this->renderView('external/register', $params);
            }
          }
          break;
      }
    }
    if ($params ['localuser'] && !$this->getAppOwner()->isAuthentificated() && \Request::get('__confirm')) {
      $ruser = $params ['localuser'];
      $auth = $this->getAppOwner()->getAuthentificator();
      if ($auth->authDirect($ruser->user_name, $ruser->user_password, $p)) {
        $script = <<< EOT
if (window.opener) {
	window.opener.reload();
	window.close();
}else{
	window.location.reload();
}
EOT;

        if (\Request::isAJAXRequest()){
          JSUtils::renderJSTag($script, false);
        }else{
          return $this->redirect('dashboard');
        }
      }
    }
    switch ($state) {
      case Auth::ERROR_STATE :
        return $this->renderView('external/error', $params);
      case Auth::NEED_RETRY_STATE :
        return $this->renderView('external/retry', $params);
        break;
      case Auth::NEED_REMOTEAUTH_STATE :
        $url = $provider->getLoginURL($rurl);
        \Response::Redirect($url);
        break;
      case Auth::NEED_CONFIRM_LOCAL_STATE :
        return $this->renderView('external/localconfirm', $params);
      default :
        ppd('haloo unhandled state ' . $state);
        break;
    }
  }

  function openid() {
    if (!CGAF_DEBUG) {
      throw new UnimplementedException ();
    }
    $this->addClientAsset(array(
        'openid.css',
        'openid.js'
    ));
    $vars = array(
        'providers' => OpenId::getSupportedProviders(),
        'msg' => '',
        'error' => '',
        'success' => '',
        'response' => null
    );
    $openid = OpenId::getInstance();
    $action = \Request::get('action');
    if ($action) {
      try {
        switch (strtolower($action)) {
          case 'verify' :
            return $openid->Verify();
            break;
          case 'finish' :
            $vars ['response'] = $openid->finish();
          default :
            break;
        }
      } catch (\Exception $ex) {
        $vars ['error'] .= "<p>" . $ex->getMessage() . '</p>';
      }
    }
    return parent::render(null, $vars);
  }

  function logout() {
    $confirm = Request::get('__confirm');
    if (!$confirm) {
      return $this->render('confirm', array(
          'title' => __('auth.signout.confirm.title'),
          'descr' => __('auth.signout.confirm.descr')
      ));
    }
    if (strtolower($confirm === 'yes') || $confirm === '1') {
      $this->getAppOwner()->LogOut();
    }
    \Response::Redirect(BASE_URL . '?__t=' . time());
  }

  function Index() {
    $retval = false;
    $appOwner = $this->getAppOwner();
    $redir = urldecode(Request::get('original_url', Request::get("redirect")));
    if ($redir) {
      //Prevent recursive
      $u = \URLHelper::explode($redir);
      $rc = implode('/', $u['path']);
      if ($rc === $this->getControllerName() || $rc === $this->getControllerName() . '/index') {
        $redir = null;
      }

      if (isset($u['query_params']['__c'])) {
        $a = isset($u['query_params']['__a']) ? $u['query_params']['__a'] : 'index';
        if ($u['query_params']['__c'] === $this->getControllerName() && $a === 'index') {
          $redir = null;
        }
      }
    }

    $providers = array();
    $msg = Request::get('__msg',$this->getAppOwner()->getVars('__msg'));
    if (!$appOwner->isAuthentificated()) {
      if ($appOwner->isValidToken()) {
        //$args = Request::gets ();
        $msg = $this->getAppOwner()->getVars('__msg');

        try {
          $retval = $this->getAppOwner()->Authenticate();
        } catch (\Exception $e) {
          $msg = $e->getMessage();
        }
        if (!$msg && !$retval) {
          $msg = $this->getAppOwner()->getAuthentificator()->getLastError();
        }
        if ($retval) {
          $redir = \URLHelper::addParam($redir, array(
              '__t' => time()
          ));
        } else {

          $redir = \URLHelper::addParam(BASE_URL . '/auth/login/', array(
              '__t' => time(),
              '__msg' => urlencode($msg)
          ));
        }
        if (Request::isJSONRequest()) {
          if (!$retval) {
            return new JSONResult (false, array(
                "message" => $msg,
                "__token" => $this->getAppOwner()->getToken()
            ));
          } else {
            return new JSONResult (true, array(
                "_result" => true,
                "_redirect" => $redir
            ));
          }
        }
      } elseif (Request::get('__token')) {
        $msg = __('error.invalidtoken', 'Invalid Token');
        if (Request::isAJAXRequest()) {
          throw new SystemException ($msg);
        }
      }
    }
    if ($appOwner->isAuthentificated()) {
      \Response::Redirect($redir ? $redir : BASE_URL . 'user/dashboard/');
    } else {
      if ($this->getAppOwner()->getConfig('auth.External.enabled', true)) {
        $providers = $this->getAppOwner()->getConfig('auth.External.providers', array(
            'google',
            'facebook',
            'oauth'
        ));
      }
    }
    if (Request::get('__token', null, true, 'p')) {
      $this->getAppOwner()->resetToken();
    }
    if (!\Request::isDataRequest()) {
      $retval = parent::render('form/login', array(
          'providers' => $providers,
          'redirect' => $redir,
          '__msg' => $msg
      ), true);
    }else{
      return new JSONResult(0,"invalid username/password");
    }
    return $retval;
  }
}

?>
