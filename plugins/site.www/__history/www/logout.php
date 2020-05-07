<?php

/* ========================================================================
 * $Id: logout.php 199 2018-08-30 19:25:13Z onez $
 * 
 * Email: www@onez.cn
 * QQ: 6200103
 * HomePage: http://www.onezphp.com
 * ========================================================================
 * Copyright 2016-2017 佳蓝科技.
 * 
 * ======================================================================== */

!defined('IN_ONEZ') && exit('Access Denied');
onez('cache')->cookie($G['this']->cname,'del');
?>
<script type="text/javascript">
location.href='<?php echo onez()->href('/admin/index.php')?>';
</script>