<?php
use System\Web\JS\CGAFJS;
use System\Web\JS\JSUtils;
$appOwner = isset ( $appOwner ) ? $appOwner : $this->getAppOwner ();
if ($this->getAppOwner ()->parent)
	return;
$share = null;
$footer = \CGAF::isInstalled() ? $this->getController()->renderContent ( 'footer',null, false, true, null, false, $appOwner->getAppId()) : null;
echo '</div><!-- EOF wrapper-inner -->';
echo '</div><!-- EOF wrapper -->';
echo $footer ? '<div>' . $footer . '</div>' : '';
echo '<footer id="wrapper-footer" class="footer" data-role="footer" data-position="fixed">';
echo '<p class="pull-right cgaf-powered"><a href="' . BASE_URL . 'about/cgaf/">Powered by CGAF ' . CGAF_VERSION. '</a>'.(CGAF_DEBUG ? '&nbsp;<span class="label label-warning">DEBUG MODE</span>' : '').'</p>';
echo $this->render ( 'shared/footer-common', true );
//echo '<span class="r-address"> Your IP' . $_SERVER ['REMOTE_ADDR'] . '</span>';
echo '</footer>';

echo '</div><!-- EOF page/wrapper -->';
//echo '</div><!--- EOF Page-->';

if (CGAF::isDebugMode ()) {
	echo $this->render ( "app-log", true );
}
echo $appOwner->renderClientAsset ( "js" );
if (Request::get ( '__msg' )) {
	echo '$("#sysmessage").html(\'' . Request::get ( '__msg' ) . '\').show(\'slow\'); ';
}
//echo '</script>';
echo JSUtils::renderJSTag ( $appOwner->getClientDirectScript (), false );
echo CGAFJS::Render ( $appOwner->getClientScript () );
echo '</body>';
echo '</html>';
?>
