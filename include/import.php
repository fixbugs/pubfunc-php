<?php
/**
 * 系统初始化加载文件
 */
 
//设置mb编码
mb_internal_encoding('utf8');

//设置调试方式
define('_DEBUG_',  isset($_GET['debug'])?true:false);
define('E_LEVEL', E_ERROR);
set_include_path( 
    _FRAMEWORK_ . _PS_ . 
    _PLUGIN_ . _PS_ . 
    _LIBRARY_ . _PS_ .
    _APP_ . '_model' . _PS_ .
    get_include_path()
);

//first
require(_FRAMEWORK_ . _DIR_SEPARATOR_ . 'loader.php');
if(PHP_OS == 'WINNT'){
    require_once(_LIBRARY_ . 'couchbase.php');
}

$_base = C('base.php');
