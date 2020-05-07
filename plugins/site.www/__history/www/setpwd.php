<?php

/* ========================================================================
 * $Id: setpwd.php 2186 2018-08-30 19:17:48Z onez $
 * 
 * Email: www@onez.cn
 * QQ: 6200103
 * HomePage: http://www.onezphp.com
 * ========================================================================
 * Copyright 2016-2017 佳蓝科技.
 * 
 * ======================================================================== */

!defined('IN_ONEZ') && exit('Access Denied');
define('CUR_URL','/setpwd.php');

$G['title']='修改密码';

$form=onez('admin')->widget('form')
  ->set('title',$G['title'])
  ->set('values',array())
;

$form->add(array('label'=>'旧密码','type'=>'password','key'=>'password0','hint'=>'请输入您的旧密码','notempty'=>'旧密码不能为空','value'=>''));
$form->add(array('label'=>'新密码','type'=>'password','key'=>'password1','hint'=>'请输入您的新密码','notempty'=>'新密码不能为空','value'=>''));
$form->add(array('label'=>'确认新密码','type'=>'password','key'=>'password2','hint'=>'请再次输入新密码','notempty'=>'确认新密码不能为空','value'=>''));

#处理提交
if($onez=$form->submit()){
  $password0=$onez['password0'];
  $password1=$onez['password1'];
  $password2=$onez['password2'];
  $password1!=$password2 && onez()->error('再次密码不一致');
  $T=$G['this']->data()->open('member')->one("id='$G[userid]'");
  md5($password0)!=$T['password'] && onez()->error('旧密码不正确');
  $item=array();
  $item['password']=md5($password2);
  $G['this']->data()->open('member')->update($item,"id='$G[userid]'");
  onez()->ok('修改密码成功','close');
}

onez('admin')->header();
?>
<section class="content-header">
  <h1>
    修改密码
  </h1>
  <ol class="breadcrumb">
    <li>
      <a href="<?php echo onez()->href('/')?>">
        <i class="fa fa-dashboard">
        </i>
        管理首页
      </a>
    </li>
    <li class="active">
      <?php echo $G['title'];?>
    </li>
  </ol>
</section>
<section class="content">
<div class="row">
  <div class="col-lg-12">
  
<form method="post" id="form-common" class="">
<div class="box box-info">
  <div class="box-header with-border">
    <h3 class="box-title"><?php echo $G['title']?></h3>
  </div><!-- /.box-header -->
  <div class="box-body">
	  <?php echo $form->code();?>
  </div><!-- /.box-body -->
  <div class="box-footer">
	  <button class="btn btn-success" type="submit">保存修改</button>
  </div>
</div>
<input type="hidden" name="action" value="save">
</form>

  
  </div>
</div>
</section>
<?php 
echo $form->js();
onez('admin')->footer();