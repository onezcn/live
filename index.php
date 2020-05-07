<?php
include_once(dirname(__FILE__).'/lib/onezphp.php');
onez('debug')->showerror();
$site_p_token='oss.live';
if(file_exists(ONEZ_ROOT.'/config/siteinfo.php')){
  include(ONEZ_ROOT.'/config/siteinfo.php');
}
onez()->set('option',array('ver'=>'1.0'));
$__ptoken=onez()->gp('_p');
$__method=onez()->gp('_m');
empty($__ptoken) && $__ptoken=$site_p_token;
empty($__method) && $__method='index';
$G['href_extra']=array();
$__ptoken!=$site_p_token && $G['href_extra']['_p']=$__ptoken;
$__method!='index' && $G['href_extra']['_m']=$__method;
$G['this']=onez($__ptoken);
$G['this']->init();
$G['this']->set('option',array('ver'=>'1.0'));
$G['this']->$__method();
