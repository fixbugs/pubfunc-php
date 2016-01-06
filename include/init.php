<?php
/**
 * 系统初始化文件
 */

//设置mb编码
mb_internal_encoding('utf8');

//设置调试方式

define('_DEBUG_', $_base['debug']);
define('_DEV_', $_base['is_dev']);
define('APP_TRACE', $_base['app_debug']);