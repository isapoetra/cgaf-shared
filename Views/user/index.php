<?php
$controller = $this->getController();
if ($appOwner->isAuthentificated()) {
  echo \Convert::toString($controller->profile());
}else{
  echo $controller->renderContent('stats');
}
?>
