<?php

/* ========================================================================
 * $Id: index.php 983 2020-05-07 17:31:42Z onez $
 * 
 * Email: www@onez.cn
 * QQ: 6200103
 * HomePage: http://www.onezphp.com
 * ========================================================================
 * Copyright 2016-2017 佳蓝科技.
 * 
 * ======================================================================== */

!defined('IN_ONEZ') && exit('Access Denied');
$G['title']='佳蓝开源系列-实时音视频';
$tmpId='';
$tmpKey=onez()->gp('tmpKey');
if($tmpKey){
  list($tmpId,$tmpIp,$tmpTime)=explode("\t",onez()->strcode($tmpKey,'DECODE'));
}
if($tmpId){
  $myIp=onez()->ip();
  if($tmpIp!=$myIp){
    $tmpId='';
  }
}
if(!$tmpId){
  $tmpId=uniqid();
  $tmpIp=$myIp;
  $tmpTime=time();
  onez()->location(onez()->href('/index.php?tmpKey='.onez()->strcode("$tmpId\t$tmpIp\t$tmpTime",'ENCODE')));
}
$G['userid']=$tmpId;

onez('factory.im')->init(array(
  'server'=>'ws://www.onez.cn:20201',
  'serverS'=>'wss://www.onez.cn/im/',
  'apiurl'=>'http://www.onez.cn:20203/api',
));
$G['footer'].=onez('factory.im')->pc();

$G['footer'].=onez('ui')->css($G['this']->url.'/css/onez.live.css');
$G['footer'].=onez('ui')->js($G['this']->url.'/js/onez.live.js');
$G['footer'].=onez('ui')->js($G['this']->url.'/js/adapter.js');

onez('template')->set('path','/www/live');
onez('template')->index();
?>