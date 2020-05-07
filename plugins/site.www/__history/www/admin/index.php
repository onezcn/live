<?php

/* ========================================================================
 * $Id: index.php 592 2018-09-01 16:37:05Z onez $
 * 
 * Email: www@onez.cn
 * QQ: 6200103
 * HomePage: http://www.onezphp.com
 * ========================================================================
 * Copyright 2016-2017 佳蓝科技.
 * 
 * ======================================================================== */

!defined('IN_ONEZ') && exit('Access Denied');
define('CUR_URL','/admin/index.php');
foreach(onez('admin')->menu as $k=>$v){
  if(!$v['href'] || $v['href']=='/admin/index.php'){
    continue;
  }
  onez()->location(onez()->href($v['href']));
}
$G['title']='管理首页';
onez('admin')->header();
$response = onez()->post('http://www.onezphp.com/api/usersite.php', http_build_query(array(
  'action'=>'admin.default',
  'sitehash'=>onez('onez')->sitehash(),
)));
$json=json_decode($response,1);
if(!empty($json) && !empty($json['html'])){
  echo $json['html'];
}
onez('admin')->footer();