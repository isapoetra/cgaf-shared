<?php
use System\Web\Utils\HTMLUtils;

$url = \URLHelper::add(APP_URL, $controller->getControllerName() . '/detail/');
if (!@$rows) {
  return '';
}
echo '<div class="news-list">';
if (@$title) {
  echo '<div class="ui-widget-header">' . $title . '</div>';
}
$cd = null;
$first = true;
$ncurl = URLHelper::add(APP_URL, '/news/bytag/');
foreach ($rows as $row) {
  $d = new \DateTime($row->date_created);
  $dmy = $d->format('dmY');
  if ($cd !== $dmy) {
    if (!$first) {
      echo '</ul>';
      echo '</div>';
    }
    $first = false;
    echo '<div class="sub-date">';
    echo '<div class="dateinfo">';
    echo '<span class="day">' . $d->format('d') . '</span>';
    echo '<span class="month">' . $d->format('M') . '</span>';
    echo '<span class="year">' . $d->format('Y') . '</span>';
    echo '</div>';
    echo '<ul>';
  }
  echo '<li>';
  echo '<span class="time">' . $d->format('H:i') . '</span>';
  echo HTMLUtils::renderLink($url . '&id=' . $row->id, $row->title);
  echo '<div class="content">';
  //author
  if ($row->fullname) {
    echo '<span class="author">';
    echo '<span class="by">' . __('news.by', 'by') . '&nbsp;</span>';
    $uurl = URLHelper::add($appOwner->getAppUrl(), '/user/profile/?id=' . $row->user_created);
    echo HTMLUtils::renderLink($uurl, $row->fullname);
    echo '</span>';
  }
  echo '<div class="short-descr">' . $row->short_descr . '</div>';
  echo '<div class="read-more">';
  echo HTMLUtils::renderLink($url . '&id=' . $row->id, __('news.readmore', 'Read More'));
  $row->news_cat = $row->news_cat ? $row->news_cat : 'news';
  $cat = explode(',', $row->news_cat);
  echo '<div class="cat-container">';
  echo '<ul class="cat">';
  foreach ($cat as $c) {
    echo '<li class="' . strtolower($c) . '">';
    echo HTMLUtils::renderLink($ncurl . '?id=' . $c, __('news.' . $c, $c));
    echo '</li>';
  }
  echo '</ul>';
  echo '</div>';
  echo '</div>';
  echo '</div>';
  echo '</li>';
  if ($cd !== $dmy) {
    $cd = $dmy;
  }
}
echo '</div>';
?>