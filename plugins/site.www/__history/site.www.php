<?php

/* ========================================================================
 * $Id: site.www.php 5352 2018-09-07 19:14:23Z onez $
 * 
 * Email: www@onez.cn
 * QQ: 6200103
 * HomePage: http://www.onezphp.com
 * ========================================================================
 * Copyright 2016-2017 佳蓝科技.
 * 
 * ======================================================================== */

!defined('IN_ONEZ') && exit('Access Denied');
#名称：网站基础插件
#标识：site.www

class onezphp_site_www extends onezphp{
  function __construct(){
    
  }
  function __call($name,$arguments){
    foreach(onez('onez')->addons() as $ptoken=>$p){
      if (is_callable(array($p, $name))) {
        return call_user_func_array(array($p, $name), $arguments);
      }
    }
  }
  function option($key=false,$default=false){
    return $this->myoption($key,$default);
  }
  function option_set($arr){
    return $this->myoption_set($arr);
  }
  function siteinfo(){
    global $G;
    $item=$this->myoption();
    $appid=$item['onez_appid'];
    $appkey=$item['onez_appkey'];
    $hash=onez()->gp('hash');
    if($hash==md5("$appid\t$appkey")){
      $A=array('sitehash'=>onez('onez')->sitehash(),'addons'=>array_keys(onez('onez')->addons()));
      $this->siteinfo_add($A);
      $A['PHP_VERSION']=PHP_VERSION;
      $A['os']=php_uname();
      $A['sapi']=$_SERVER['SERVER_SOFTWARE'];
      $A['system']=PHP_SHLIB_SUFFIX == 'dll'?'Windows':'Linux';
      $A['freespace']=function_exists('disk_free_space')?disk_free_space(ONEZ_ROOT):'unknow';
      $A['upload_max_filesize']=@ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'unknow';
      $A['post_max_size']=@ini_get('post_max_size') ? ini_get('post_max_size') : 'unknow';
      $extensions=onez()->gp('extensions');
      if($extensions){
        $A['extensions']=array();
        foreach(explode(',',$extensions) as $v){
          $A['extensions'][$v]=extension_loaded($v)?'Y':'N';
        }
      }
      $functions=onez()->gp('functions');
      if($functions){
        $A['functions']=array();
        foreach(explode(',',$functions) as $v){
          $A['functions'][$v]=function_exists($v)?'Y':'N';
        }
      }
      $classes=onez()->gp('classes');
      if($classes){
        $A['classes']=array();
        foreach(explode(',',$classes) as $v){
          $A['classes'][$v]=class_exists($v)?'Y':'N';
        }
      }
      onez()->output($A);
    }
    header("HTTP/1.0 404 Not Found");
  }
  function init(){
    global $G;
    $G['this']->data_alias=$this->cname;
  }
  function initFiles(){
    $inits=array();
    foreach(func_get_args() as $path){
      $step=0;
      while($step<=3){
        $step++;
        if($path=='.' || $path=='' || $path=='/'){
          break;
        }
        $inits[]=$path;
        if(file_exists($path.'/lib/onezphp.php')){
          break;
        }
        if($path==dirname($path)){
          break;
        }
        $path=dirname($path);
      }
    }
    $inits=array_reverse($inits);
    $newInits=array();
    foreach($inits as $v){
      $initFile=$v.'/init.php';
      if(file_exists($initFile)){
        $newInits[]=$initFile;
      }
    }
    return $newInits;
  }
  function index(){
    global $G;
    $G['this']->init();
    $mod=onez()->gp('mod');
    (!$mod || $mod=='/') && $mod='index.php';
    $mod=preg_replace('/[\.\/]+\//i','/',$mod);
    $mod=trim($mod,'/');
    $p=$this;
    while(1){
      $classname=get_class($p);
      $parent=get_parent_class($p);
      if($parent=='onezphp'){
        break;
      }
      $p=false;
      foreach($G['nodes'] as $pt=>$v){
        if(get_class(onez($pt))==$parent){
          $p=onez($pt);
          onez('call')->sysplugins[]=$pt;
          if($pt!=$G['this']->token){
            onez('call')->hide($pt);
          }
          break;
        }
      }
      $pFile=$p->path.'/www/'.$mod;
      if(file_exists($pFile)){
        if($p->token!='site.www'){
          foreach($this->initFiles(onez('site.www')->path.'/www/'.$mod) as $file){
            include_once($file);
          }
        }
        $p->www();
        return;
      }
      if(!$p){
        break;
      }
    }
    $this->www();
  }
  
  function m(){
    global $G;
    $G['this']->init();
    $action=onez()->gp('action');
    if(strpos($action,'page_id_')!==false){
      $navID=(int)substr($action,8);
      $T=$G['this']->data()->open('navs')->record("1 order by step,id");
      if($navID>0){
        $navID--;
        $nav=$T[$navID];
        parse_str('action='.$nav['href'],$info);
        foreach($info as $k=>$v){
          $_REQUEST[$k]=$_GET[$k]=$v;
        }
      }
    }
    onez('m')->add_tmpl_path(dirname(__FILE__).'/tmpls');
    onez('m')->auto();
  }
  function avatar($userid){
    global $G;
  	$myid = sprintf("%09d", $userid);
  	$dir1 = substr($myid, 0, 3);
  	$dir2 = substr($myid, 3, 2);
  	$dir3 = substr($myid, 5, 2);
    $avatarFile='/avatars/'.$G['this']->cname.'/'.$dir1.'/'.$dir2.'/'.$dir3.'/'.substr($myid, -2).'.jpg';
    if(file_exists(ONEZ_CACHE_PATH.$avatarFile)){
      return onez('m.plugin')->thumb(ONEZ_CACHE_URL.$avatarFile.'?t='.filemtime(ONEZ_CACHE_PATH.$avatarFile)).'?t='.filemtime(ONEZ_CACHE_PATH.$avatarFile);
    }else{
      return onez('m.plugin')->thumb($this->url.'/images/avatar.jpg');
    }
  }
  function avatar_set($userid,$url){
    global $G;
    $usertype=$this->usertype();
  	$myid = sprintf("%09d", $userid);
  	$dir1 = substr($myid, 0, 3);
  	$dir2 = substr($myid, 3, 2);
  	$dir3 = substr($myid, 5, 2);
    $avatarFile='/avatars/'.$G['this']->cname.'/'.$dir1.'/'.$dir2.'/'.$dir3.'/'.substr($myid, -2).'.jpg';
    $data=onez()->post($url);
    
    onez()->write(ONEZ_CACHE_PATH.$avatarFile,$data);
    return $this;
  }
}