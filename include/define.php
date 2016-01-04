<?php
/**
 * 定义系统常量
 */ 

define('_DIR_SEPARATOR_',DIRECTORY_SEPARATOR);
define('_DS_', 			_DIR_SEPARATOR_);
define('_PS_', 			PATH_SEPARATOR);
define('_ROOT_', 		dirname(dirname(__FILE__)) . _DIR_SEPARATOR_);
define('_APP_', 		_ROOT_ . 'app' . _DIR_SEPARATOR_);
define('_FRAMEWORK_', 	_ROOT_ . 'framework' . _DIR_SEPARATOR_);
define('_PLUGIN_', 		_ROOT_ . 'plugin'  . _DIR_SEPARATOR_);
define('_CONFIG_',		_ROOT_ . 'config' . _DIR_SEPARATOR_);
define('_LIBRARY_',		_ROOT_ . 'library' . _DIR_SEPARATOR_);
define('_INCLUDE_',		_ROOT_ . 'include' . _DIR_SEPARATOR_);

define('_DOMAIN_', 		'http://' . 'pubfunc.goitt.com');
define('_URL_', 		_DOMAIN_ . $_SERVER['REQUEST_URI']);

define('_CACHE_DIR_',   '/test/');//可写临时缓存目录
define('_DATA_DIR_',    '/upload/');  //可写上传数据目录

define('_IS_AJAX_',		isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? true : false);
define('_MAGIC_QUOTES_GPC_', get_magic_quotes_gpc() ? true : false);
define('_WRITE_TO_PUB_COMDB_', true);  //是否需要多写一份数据到pub_comdb中
//define('_SESSION_AUTO_START_', true);
define('_NOW_', time());
define('APP_TRACE', true);

//缓存前缀
define('PUB_CACHE_PREFIX','pubfunc_');
