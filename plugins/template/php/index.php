<?php

/* ========================================================================
 * $Id: index.php 351 2020-05-06 16:29:29Z onez $
 * 
 * Email: www@onez.cn
 * QQ: 6200103
 * HomePage: http://www.onezphp.com
 * ========================================================================
 * Copyright 2016-2017 佳蓝科技.
 * 
 * ======================================================================== */

!defined('IN_ONEZ') && exit('Access Denied');
onez('ui')->init();
onez('ui')->header();
onez('template')->header();
$indexFile=$G['this']->indexTpl(onez('template')->get('path'));
if($indexFile){
  include($indexFile);
}else{
  echo '<div id="app" class="app"><div id="content"></div></div>';
}
onez('template')->footer();
onez('ui')->footer();
