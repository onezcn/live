<?php

/* ========================================================================
 * $Id: template.php 7992 2020-05-06 16:21:41Z onez $
 * 
 * Email: www@onez.cn
 * QQ: 6200103
 * HomePage: http://www.onezphp.com
 * ========================================================================
 * Copyright 2016-2017 佳蓝科技.
 * 
 * ======================================================================== */

!defined('IN_ONEZ') && exit('Access Denied');
#名称：佳蓝模板引擎
#标识：template

class onezphp_template extends onezphp{
  var $tpl=false;
  var $noTpl=true;
  var $Components=array();
  function __construct(){
    $this->tpl=$this;
  }
  function loadTemplate($ptoken){
    if(!onez()->exists($ptoken)){
      exit('模板'.$ptoken.'不存在');
    }
    $this->tpl=onez($ptoken);
    $this->noTpl=($ptoken==$this->token);
    return $this;
  }
  function header(){
    global $G;
    echo onez('ui')->css($this->url.'/res/layui/css/layui.css');
    echo onez('ui')->css($this->url.'/res/nutui/nutui.min.css');
    echo onez('ui')->css($this->url.'/css/onez.css');
    echo $this->loadComponents();
  }
  function footer(){
    global $G;
    echo onez('ui')->js($this->url.'/res/layui/layui.all.js');
    echo onez('ui')->js($this->url.'/js/vue.min.js');
    echo onez('ui')->js($this->url.'/res/nutui/nutui.min.js');
    echo onez('ui')->js($this->url.'/js/onez.js');
    echo '<script type="text/javascript">onez.start();</script>';
  }
  function pagelimit($pagesize=12){
    global $G;
    $G['pagesize']=$pagesize;
    $G['page']=$page=max(1,(int)onez()->gp('page'));
    return' limit '.(($page-1)*$pagesize).','.$pagesize;
  }
  function page_init($T,$opt=array()){
    global $G,$A,$record;
    $page=max(1,(int)onez()->gp('page'));
    if($page==1){
      if(!$T){#没有数据
        $record[]=array(
          'type'=>'none',
          'text'=>$opt['noneTip']?$opt['noneTip']:'记录不存在',
        );
        return false;
      }
    }
    if(!$G['pagesize']|| count($T)<$G['pagesize']){
      $A['hasMore']=false;
    }else{
      $A['hasMore']=true;
    }
    return true;
  }
  function loadComponents(){
    global $G,$A,$record;
    $css=$html=array();
    $path=onez('template')->get('path');
    $o=explode('/',$path);
    $plat=$o[1];
    $components=array();
    $this->getComponent($components,$this->tpl->path.'/components/'.$plat);
    $this->getComponent($components,$this->tpl->path.'/components'.$path);
    $this->getComponent($components,$G['this']->path.'/components/'.$plat);
    $this->getComponent($components,$G['this']->path.'/components'.$path);
    foreach($components as $token=>$v){
      list($_vue,$_js,$_css)=$v;
      if($_vue){
        $html[]='<script type="x-template" id="temp-'.$token.'">';
        $html[]=$_vue;
        $html[]='</script>';
      }
      if($_js){
        $html[]='<script type="x-code" id="js-'.$token.'">';
        $html[]=$_js;
        $html[]='</script>';
      }
      if($_css){
        $css[]=$_css;
      }
    }
    if($css){
      $html[]='<style type="text/css">';
      $html[]=implode("\n",$css);
      $html[]='</style>';
    }
    return implode("\n",$html);
  }
  function getComponent(&$components,$path,$pre=''){
    $glob=glob($path.'/'.$pre.'*');
    if($glob){
      foreach($glob as $v){
        $vue=$js=$css='';
        if(is_dir($v)){
          $token=basename($v);
          if(file_exists($v.'/'.$token.'.vue')){
            $vue.=onez()->read($v.'/'.$token.'.vue');
          }
          if(file_exists($v.'/'.$token.'.js')){
            $js.=onez()->read($v.'/'.$token.'.js');
          }
          if(file_exists($v.'/'.$token.'.css')){
            $css.=onez()->read($v.'/'.$token.'.css');
          }
          if(file_exists($v.'/'.$token.'.less')){
            $_css=onez()->read($v.'/'.$token.'.less');
            if($_css){
              $css.=onez('less')->tocss($_css);
            }
          }
          $vue && $components[$token]=array($vue,$js,$css);
          continue;
        }
        if(substr($v,-4)!='.php'){
          continue;
        }
        $token=substr(basename($v),0,-4);
        $json=array();
        include($v);
        if($json['css']){
          $css.=$json['css'];
        }
        if($json['less']){
          $css.=onez('less')->tocss($json['less']);
          unset($json['less']);
        }
        if($json['html']){
          $vue.=$json['html'];
        }
        if($json['vue']){
          $vue.=$json['vue'];
        }
        if($json['js']){
          $js.=$json['js'];
        }
        $vue && $components[$token]=array($vue,$js,$css);
      }
    }
  }
  function index(){
    global $G,$A,$record;
    $_ajax_action=onez()->gp('_ajax_action');
    if($_ajax_action){
      $json=array();
      $json['status']='success';
      $path=onez('template')->get('path');
      $_ajax_action=preg_replace('/[\.]+\//i','',$_ajax_action);
      $actionFile=$path.'/'.$_ajax_action.'.php';
      if(file_exists($G['this']->path.'/tmpls'.$actionFile)){
        include($G['this']->path.'/tmpls'.$actionFile);
      }
      onez()->output($json);
    }
    
    $_action=onez()->gp('_action');
    if($_action=='getData'){
      $A=array();
      $A['ver']='1.0';
      $A['action']=onez()->gp('action');
      $record=array();
      $action='action='.str_replace('?','&',$A['action']);
      parse_str($action,$info);
      $action=$info['action'];
      foreach($info as $k=>$v){
        $_GET[$k]=$_REQUEST[$k]=$v;
      }
      $path=onez('template')->get('path');
      $action=preg_replace('/[\.]+\//i','',$action);
      $actionFile=$path.'/'.$action.'.php';
      if($action=='welcome'){
        if(file_exists($G['this']->path.'/tmpls'.$actionFile)){
          include($G['this']->path.'/tmpls'.$actionFile);
        }
        $this->tpl->welcome();
      }else{
        if(!file_exists($G['this']->path.'/tmpls'.$actionFile)){
          //onez()->error('接口'.$actionFile.'不存在');
        }
        include($G['this']->path.'/tmpls'.$actionFile);
      }
      $A['_ajax_action']=$action;
      $record && $A['record']=$record;
      $json=array();
      $json['status']='success';
      $json['data']=$A;
      if(defined('IS_POSTS_MODE')){
        return $json;
      }
      onez()->output($json);
    }elseif($_action=='_component'){
      $type=onez()->gp('type');
      $json=array('type'=>$type);
      
      $path=onez('template')->get('path');
      $o=explode('/',$path);
      $plat=$o[1];
      $type=preg_replace('/[\.]+\//i','',$type);
      
      $components=array();
      $this->getComponent($components,$this->tpl->path.'/components/'.$plat,$type);
      $this->getComponent($components,$this->tpl->path.'/components'.$path,$type);
      $this->getComponent($components,$G['this']->path.'/components/'.$plat,$type);
      $this->getComponent($components,$G['this']->path.'/components'.$path,$type);
      if($components[$type]){
        return array(
          'html'=>$components[$type][0],
          'js'=>$components[$type][1],
          'css'=>$components[$type][2],
        );
      }
      if(defined('IS_POSTS_MODE')){
        return $json;
      }
      onez()->output($json);
    }elseif($_action=='posts'){
      define('IS_POSTS_MODE',1);
      $json=array();
      $json['status']='success';
      $_postdatas=$_REQUEST['_postdatas'];
      if($_postdatas){
        foreach(json_decode($_postdatas,1) as $action=>$postdata){
          $_action=$action;
          if(strpos($action,'?')!==false||strpos($action,'&')!==false){
            $action='action='.str_replace('?','&',$action);
            parse_str($action,$info);
            foreach($info as $k=>$v){
              if($k=='action'){
                $action=$v;
              }else{
                $_REQUEST[$k]=$_GET[$k]=$v;
              }
            }
          }
          if($action=='posts'){
            continue;
          }
          $REQUEST=$_REQUEST;
          $GET=$_GET;
          $POST=$_POST;
          
          $_REQUEST['_action']=$action;
          foreach($postdata as $k=>$v){
            $_POST[$k]=$v;
            $_REQUEST[$k]=$v;
          }
          $json['actions'][$_action]=$this->index();
          
          $_REQUEST=$REQUEST;
          $_GET=$GET;
          $_POST=$POST;
        }
      }
      onez()->output($json);
    }
    include($this->tpl->path.'/php/index.php');
  }
}