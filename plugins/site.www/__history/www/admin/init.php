<?php

/* ========================================================================
 * $Id: init.php 2923 2018-09-07 18:34:05Z onez $
 * 
 * Email: www@onez.cn
 * QQ: 6200103
 * HomePage: http://www.onezphp.com
 * ========================================================================
 * Copyright 2016-2017 佳蓝科技.
 * 
 * ======================================================================== */

!defined('IN_ONEZ') && exit('Access Denied');
!defined('ONEZ_SITENAME')&&define('ONEZ_SITENAME','网站名称');
!defined('ONEZ_SITELOGO')&&define('ONEZ_SITELOGO','//cdn.onez.cn/2018/0830/2018083019142988500001.png');


$login=onez('login')->init($G['this']->cname,0);
$G['userid']=(int)$login['id'];

if(!$G['userid']){
  function _login_callback($username,$password,$password_md5){
    global $G;
    $T=$G['this']->data()->open('member')->one("key2='$username' and key3='$password_md5'");
    if(!$T){
      #如果没有任何用户，刚自动创建新账号
      
      $T=$G['this']->data()->open('member')->one("1");
      if(!$T){
        $item=array();
        $item['key1']='admin';
        $item['key2']=$username;
        $item['key3']=$password_md5;
        $item['nickname']='超级管理员';
        $G['this']->data()->open('member')->insert($item);
        $T=$G['this']->data()->open('member')->one("key2='$username' and key3='$password_md5'");
        return "$T[id]\t$username";
      }
      return false;
    }
    return "$T[id]\t$username";
  }
  onez('login')->is_register=0;
  $G['title']=ONEZ_SITENAME;
  onez('login')->tip='<p class="tip">欢迎登录超级管理中心！</p>';
  $header=onez('ui')->css('//www.onez.cn/?_p=background&_m=login&site='.urlencode(onez()->homepage()));
  $header.='<div class="background fullscreen" style="position: absolute;left:0;top: 0;width: 100%"></div>';
  $header.='<script type="text/javascript">
  $(function(){
    $(".img-circle").addClass("animated bounceInDown");
  });
  </script>';
  #$header.=onez('html5.star')->code('.background');
  onez('login')->set('header',$header);

  $img_title='http://font.onezphp.com/?_m=show&type=login.head&text='.urlencode(ONEZ_SITENAME);
  $cache_file='/plugins/'.$G['this']->token.'/login_tip/'.md5($img_title).'.png';
  if(!file_exists(ONEZ_CACHE_PATH.$cache_file) || filesize(ONEZ_CACHE_PATH.$cache_file)<100){
    onez()->write(ONEZ_CACHE_PATH.$cache_file,onez()->post($img_title));
  }
  //$img_title=ONEZ_CACHE_URL.$cache_file;
  #$img_title='';

  onez('login')->set('avatar',ONEZ_SITELOGO);
  onez('login')->set('head','<img src="'.$img_title.'" alt="'.ONEZ_SITENAME.'" />');
  onez('login')->init($G['this']->cname);
  exit();
}
onez('admin')->style='skin-red';#自定义皮肤
$G['user']=$G['this']->data()->open('member')->one("id='$G[userid]'");
$G['avatar']=ONEZ_SITELOGO;
$G['nickname']=$G['user']['nickname'];
$G['gradename']='超级管理平台';
onez('admin')->menu_top_right.='<ul class="nav navbar-nav">
  <li><a href="'.onez()->href('/setpwd.php').'" class="onez-miniwin">修改密码</a></li>
</ul>';

$Menu=array();
onez('admin.menu')->init('admin',$Menu);
if(!$Menu){
  $Menu[]=array(
    'name'=>'管理首页',
    'href'=>'/admin/index.php',
  );
}
onez('admin')->menu=$Menu;
$G['url.grade']='admin';
if(strpos(__FILE__,'/git/onezblue/')===false){
  onez('onez')->init('admin');
}