
<?php
function renderInfo(\PersonData $userInfo, $info,$mode) {
  $retval ='<div class="row">';
  $retval .= '<span class="span2">' . __ ( 'person.' . $info,__($info,ucwords($info) )) . '</span>';
  $retval .= '<span class="span6">' . $userInfo->{$info} . '</span>';
  $retval .='</div>';
  return $retval;
}
$mode =\Request::get('mode');
echo '<div class="top">';
if ($row->isMe()) {
  echo '<h3><a href="'.\URLHelper::add(APP_URL,'/person/aed/?id='.$row->person_id).'">'.$row->FullName.'</a></h3>';
  if (!$row->isprimary) {
    echo '<a class="btn btn-warning" href="'.\URLHelper::add(APP_URL,'/person/p/?id='.$row->person_id).'">set as primary</a>';
  }elseif ($row->isMe()) {
    echo '<span class="label label-info">Primary</span>';
  }
}else{
  $route = $appOwner->getRoute();
  $ori = $route['_c'] === 'person';
  echo '<h3>' .($ori ? '' : '<a href="'.\URLHelper::add(APP_URL,'/person/detail/?id='.$row->person_id).'">'). $row->FullName .($ori ? '' :'</a>'). '</h3>';
}
if ($row->isMe()) {
  echo '<div>What\'s in your brain : </div>';
}
echo '<div class="row-fluid show-grid">';
$retval = '<div class="row show-grid">';
echo '<div class="span2">';
echo ' <img src="'.$row->getImage(null,'167x125',true).'"/>';
echo '</div>';
echo '<div class="'.($actions ? 'span6' : 'span8').'">';
echo renderInfo ( $row, 'birth_date',$mode );
echo renderInfo ( $row, 'gender',$mode );
echo renderInfo ( $row, 'employment',$mode );
echo '</div>';
if ($actions) {
  echo '<div class="span2">';
  echo '<ul class="nav nav-list">';
  foreach ($actions as $act) {
    echo '<li>'.\Convert::toString($act).'</li>';
  }
  echo '</ul>';
  echo '</div>';
}
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '<hr class="delimiter"/>';
echo $this->getController()->RenderContent ( 'personal-info', array (
    'fromtab'=>true,
    'row' => $row
), true, \CGAF::APP_ID );
?>

