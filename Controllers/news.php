<?php
namespace System\Controllers;
use System\MVC\StaticContentController;

use System\ACL\ACLHelper;

use System\Template\TemplateHelper;

use System\Search\SearchResults;
use System\MVC\Controller;
class News extends StaticContentController {
  function isAllow($access='view') {
    switch ($access) {
      case 'view':
      case 'lists':
      case 'index':
      case 'images':
      case 'recents':
      case 'detail':
        return true;
    }
    return parent::isAllow($access);
  }
  function search($s, $config) {
    $retval = new SearchResults();
  }
  function assets() {
    $u = \URLHelper::explode($_REQUEST['__url']);
    $path = $u['path'];
    array_shift($path);
    $id = $path[1];
    $fname= basename($path[2]);
    $o = $this->getModel()->load($id);

    if ($o) {
      $path = $this->getInternalPath($o->id.'/assets/');
      if (is_file($path.$fname)) {
        return \Streamer::Stream($path.$fname);
      }
    }
  }
  function recents() {
    $m = $this->getModel()->reset();
    $m->where('controller='.$m->quote($this->getAppOwner()->getRoute('_c')))
    ->orderBy('date_created desc');
    return parent::renderView('lists',array(
        'rows'=>$m->loadObjects()
    ));
  }
  function getActionAlias($action) {
    switch ($action) {
      case 'read':
        return 'detail';
    }
    return parent::getActionAlias($action);
  }
  function detail($args = null, $return = null) {
    $id =\Request::get('id');
    if ($id==null) {
       $rs = explode('/',$_REQUEST['__url']);
       if (isset($rs[2]) && $rs[2]) {
         $id = $rs[2];
       }
    }
    if ($id) {
      $m = $this->getModel()->reset('detail');
      $row = $m->where('id='.$m->quote($id))->loadObject();
      if ($row) {
        switch ((int)$row->type) {
          case 0:
            $f = $this->getContentFile($row->id.'/index',true);
            if (is_file($f)) {
              $row->contents=  TemplateHelper::renderFile($f,array(
                  'baseurl' => BASE_URL,
                  'imageurl'=>\URLHelper::add(BASE_URL,'news/assets/id/'.$row->id.'/')
              ),$this);
            }
        }
        if (!isset($row->contents) || empty($row->contents)) {
          $row->contents ='';
        }
      }
      return parent::renderView(__FUNCTION__,array('row'=>$row));
    }
  }
  function Index($return=false) {
    $currentPage = \Request::get('_cp',0);
    $rpp = \Request::get('_rpp',10);
    $rows = $this
    ->getModel()
    ->reset()
    ->orderBy('date_created desc')
    ->loadObjects(null,$currentPage,$rpp);
    $pageCount = $this->getModel()->clear()->select('count(*)','c',true)->loadObject()->c;
    return $this->renderView(
        'index', array(
            'dataUrl'=>\URLHelper::add(APP_URL,'news/'),
            'currentPage'=>$currentPage,
            'pageCount'=>$pageCount,
            'rowPerPage'=>$rpp,
            'title' => __('news.recent'),
            'rows'  => $rows)
    );
  }
}
