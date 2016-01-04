<?php
/**
 * 系统入口文件
 */
require_once('include/define.php');
require_once('include/import.php');
require_once('include/init.php');
error_reporting(E_ERROR&~E_NOTICE);
die("aaa");
//请求->路由->过滤->分发->响应
$controller = Leb_Controller::getInstance();
$controller->run();
