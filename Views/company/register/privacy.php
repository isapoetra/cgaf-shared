<?php
use System\Web\Utils\HTMLUtils;
$items = Privacy::Lookup();

echo HTMLUtils::renderSelect(__('privacy.visibility','Visibility'), 'privacy_view',$items,Privacy::FRIEND_ACCESS,null);
?>