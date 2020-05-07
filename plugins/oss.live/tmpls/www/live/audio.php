<?php

/* ========================================================================
 * $Id: audio.php 334 2020-05-07 08:31:21Z onez $
 * 
 * Email: www@onez.cn
 * QQ: 6200103
 * HomePage: http://www.onezphp.com
 * ========================================================================
 * Copyright 2016-2017 佳蓝科技.
 * 
 * ======================================================================== */

!defined('IN_ONEZ') && exit('Access Denied');
$uid=onez()->gp('uid');
$A['title']='语音通话 - '.$uid;
$status=onez()->gp('status');
!$status && $status='request';
$record[]=array(
  'type'=>'im',
  'status'=>$status,
  'callType'=>'audio',
  'callTypeName'=>'语音',
  'callUid'=>$uid,
  'resurl'=>onez('oss.live')->url,
);