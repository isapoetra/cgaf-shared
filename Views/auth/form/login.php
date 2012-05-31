<?php
use System\ACL\ACLHelper;

use System\Web\Utils\HTMLUtils;
use System\Auth\Auth;
$redirect = isset ( $redirect ) ? $redirect : URLHelper::addParam ( BASE_URL, array (
    'redirect' => Request::get ( "redirect" ),
    '__t' => time ()
) );
$providers = isset ( $providers ) ? $providers : array();
$msg =\Strings::unHTML(isset ( $__msg ) ? $__msg : Request::get ( '__msg' ),ENT_NOQUOTES);
?>
<table class="table table-bordered login-form">
	<tr>
		<td width="75%" class="left"><?php echo $this->getController()->renderContent('left',null,false).'&nbsp;' ?>
		</td>
		<td class="login-container"><?php
		$cssClass = isset ( $cssClass ) ? $cssClass : 'login';
		$renderNext = isset ( $renderNext ) ? $renderNext : false;
		$json = isset ( $json ) ? $json : false;
		if ($msg) {
		  echo '<div class="alert">';
		  echo '<a class="close" data-dismiss="alert">&times;</a>';
		  echo '<strong>'.$msg.'</strong>';
		  echo '</div>';
		}
		echo HTMLUtils::beginForm ( \URLHelper::add ( APP_URL, 'auth?__t=' . time (), ($json ? '_json=1' : '') ), false, true, null, array (
		    'class' => $cssClass,
		    'id' => 'login'
		) );
		if (!\CGAF::isAllow($appOwner->getAppId(), ACLHelper::APP_GROUP)) {
		  echo HTMLUtils::renderHiddenField('__appId', \CGAF::APP_ID);
		}
		if ($redirect) {
		  echo '<input type="hidden" name="redirect" value="' . $redirect . '">';
		}
		echo '<h2>Sign In</h2>';
		echo '<div class="logon-info">';
		echo '  <div class="username">';
		echo '    <label for="username"><strong>'. __ ( 'auth.user_name' ).'</strong></label>';
		echo '    <input type="text" name="username" class="required"/>';
		echo '  </div>';
		echo '  <div class="password">';
		echo '    <label for="password"><strong>'. __ ( 'auth.user_password' ).'</strong></label>';
		echo '    <input type="password" name="password" class="required"/>';
		echo '  </div>';
		echo '</div>';
		//echo HTMLUtils::renderTextBox ( __ ( 'auth.user_name' ), 'username', null, 'class="required"', true ) . '<br/>';
		//echo HTMLUtils::renderPassword ( __ ( 'auth.user_password' ), 'password', null, 'required class="required"', true );
		//echo HTMLUtils::renderCheckbox ( __ ( 'auth.remember' ), 'remember', false );

		echo '<div class="form-actions">';
		echo HTMLUtils::renderButton ( 'submit', __ ( 'auth.login.title', 'Sign In' ), 'Login to System', array ('class'=>'btn btn-primary'), true, 'login-small.png' );
		$title =  __ ( 'auth.remember' );
		echo <<< EOT
<div class="help-inline" >
	<label class="checkbox">
		<input type="checkbox" id="remember" name="remember"/>$title
	</label>
</div>
EOT;

		echo '</div>';
		echo '<hr class="divider"/>';
		echo '<div class="action-ext">';
		echo '<div class="btn-group">';
		echo HTMLUtils::renderLink ( BASE_URL . 'user/forgetpassword/', __ ( 'auth.forgetpassword' ) ,array('class'=>'btn btn-danger'));
		echo HTMLUtils::renderLink ( BASE_URL . 'user/register/', __ ( 'auth.register' )  ,array('class'=>'btn btn-success'));
		echo '</div>';
		echo '</div>';
		echo HTMLUtils::endForm ( false, true );
		if ($providers) {
		  $script = <<<EOT
$('.auth-external').click(function(e) {
	e.preventDefault();
	var  screenX    = typeof window.screenX != 'undefined' ? window.screenX : window.screenLeft,
	screenY    = typeof window.screenY != 'undefined' ? window.screenY : window.screenTop,
	outerWidth = typeof window.outerWidth != 'undefined' ? window.outerWidth : document.body.clientWidth,
	outerHeight = typeof window.outerHeight != 'undefined' ? window.outerHeight : (document.body.clientHeight - 22),
	width    = 500,
	height   = 270,
	left     = parseInt(screenX + ((outerWidth - width) / 2), 10),
	top      = parseInt(screenY + ((outerHeight - height) / 2.5), 10),
	features = (
		'width=' + width +
		',height=' + height +
		',left=' + left +
		',top=' + top
	);
	var url =cgaf.url($(this).attr('href'),{popupmode:true}).toString();
	newwindow=window.open(url,'CGAF External Login',features);
	newwindow.onclose = function() {
		document.location.reload();
	}
	if (window.focus) {
	newwindow.focus()}
		return false;
});

EOT;
		  if (CGAF_DEBUG) {
		    $script .= <<< EOT
$('#browserid').click(function() {
  navigator.id.getVerifiedEmail(function(assertion) {
    // got an assertion, now send it up to the server for verification
    if (assertion !== null) {
      $.ajax({
        type: 'POST',
        url: '/auth/browserid',
        data: { assertion: assertion },
        success: function(res, status, xhr) {
          if (res === null) {
           //loggedOut();
		  } else {
		    console.log(arguments);
		    //window.location.reload();
          }
        },
        error: function(res, status, xhr) {
          alert("login failure" + res);
        }
      });
    } else {
      //loggedOut();
    }
  }
);
  return false;
});
EOT;
		    $appOwner->addClientAsset('https://browserid.org/include.js');
		  }

		  $appOwner->addClientScript ( $script );
		  echo '<div class="auth-external-container">';
if (CGAF_DEBUG) {
		  echo '<a href="#" id="browserid" title="Sign-in with BrowserID">';
		  echo '<img src="'.$appOwner->getLiveAsset('auth/browserid.png').'" alt="Sign in">';
		  echo '</a>';
}
		  echo '<div class="ui-widget-header">' . __ ( 'auth.alternative' ) . '</div>';
		  foreach ( $providers as $provider ) {
		    echo HTMLUtils::renderLink ( \URLHelper::add ( APP_URL, 'auth/external', 'id=' . $provider ), __ ( 'auth.' . $provider . 'title', ucfirst ( $provider ) ), array (
		        'title' => __ ( 'auth.' . $provider . 'descr', 'Signin using ' . ucfirst ( $provider ) ),
		        'class' => 'auth-external auth-external-' . $provider
		    ), 'auth/' . $provider . '.png' );
		  }
		  echo '</div>';
		}
		?>
		</td>
	</tr>
</table>
