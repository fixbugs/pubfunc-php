<?php
/**
 * 框架全局配置文件
 *
 */

//XHProf性能分析，默认关闭
//注意开启前请先安装xhprof扩展
defined('_FRM_BMEMORY_')    or define('_FRM_BMEMORY_', memory_get_usage());
defined('XHPROF_ANALYSIS')  or define('XHPROF_ANALYSIS', false);
defined('_DS_')             or define('_DS_', DIRECTORY_SEPARATOR);
defined('_PS_')             or define('_PS_', PATH_SEPARATOR);
defined('_DIR_SEPARATOR_')  or define('_DIR_SEPARATOR_', _DS_);

//输出log到http头信息中，默认关闭
defined('APP_TRACE')    or define('APP_TRACE',  false);

//是否运行cli模式
defined('_CLI_')        or define('_CLI_',      empty($_SERVER['REMOTE_ADDR']));

//是否打开调试模式，默认关闭
defined('_DEBUG_')      or define('_DEBUG_',    _CLI_);

//保存异常日志文件，默认不保存
defined('_RUNTIME_')    or define('_RUNTIME_',  '');

defined('_ROOT_')       or define('_ROOT_',     dirname(dirname(__FILE__))._DS_);
defined('_WWW_')        or define('_WWW_' ,     _ROOT_);
defined('_FRAMEWORK_')  or define('_FRAMEWORK_',dirname(__FILE__)._DS_);
defined('_CONSOLE_')    or define('_CONSOLE_',  _FRAMEWORK_.'console'._DS_);
defined('_APP_')        or define('_APP_',      _ROOT_.'app'._DS_);
defined('_PLUGIN_')     or define('_PLUGIN_',   _ROOT_.'plugin'._DS_);
defined('_CONFIG_')     or define('_CONFIG_',   _ROOT_.'config'._DS_);
defined('_CMD_')        or define('_CMD_',      _ROOT_.'command'._DS_);
defined('_WEB_')        or define('_WEB_',      _FRAMEWORK_.'web'._DS_);
defined('_DOMAIN_')     or define('_DOMAIN_',  'http://'.$_SERVER['HTTP_HOST']);
defined('_VALIDATOR_')  or define('_VALIDATOR_',_FRAMEWORK_.'validator'._DS_);

defined('_DEF_APP_')        or define('_DEF_APP_',          'default'); //默认应用名称
defined('_DEF_CONTROLLER_') or define('_DEF_CONTROLLER_',   'default'); //默认控制器
defined('_DEF_ACTION_')     or define('_DEF_ACTION_',       'index');   //默认应用方法

//快表和缓存默认使用md5值作为主键
defined('DATA_KEY_MD5') or define('DATA_KEY_MD5', true);

//快表数据存储格式，默认用JSON
defined('DATA_VALFMT_JSON') or define('DATA_VALFMT_JSON', true);

//开启PHP代码性能分析
if(XHPROF_ANALYSIS && _DEBUG_ && function_exists('xhprof_enable'))
{
    xhprof_enable(
        XHPROF_FLAGS_CPU            //记录CPU时间
        + XHPROF_FLAGS_MEMORY       //记录内存使用
        + XHPROF_FLAGS_NO_BUILTINS  //忽略内建函数，如：strlen,strpos
        ,
        array('ignored_functions'=>array('')
        )
    );
}

//加载框架函数
include_once(dirname(__FILE__)._DS_.'functions.php');
if(_CLI_)
{
    set_include_path(
        _FRAMEWORK_ . _PS_
        . _ROOT_ . _PS_
        . _APP_ . _PS_
        . _PLUGIN_ . _PS_
        . _CONSOLE_ . _PS_
        . _CMD_ . _PS_
        . _FRAMEWORK_.'model'._PS_
        . get_include_path());
}
else
{
    set_include_path(
        _FRAMEWORK_ . _PS_
        . _ROOT_ . _PS_
        . _APP_ . _PS_
        . _PLUGIN_ . _PS_
        . _WEB_ . _PS_
        . _FRAMEWORK_.'model'._PS_
        . get_include_path());
}

@ini_set('display_errors', '0');
@ini_get('error_log') ? @ini_set('log_errors', '1') : @ini_set('log_errors', '0');
!_DEBUG_ && error_reporting(0);

if(PHP_VERSION < '6.0')
{
    @ini_set('magic_quotes_runtime', 0); 
    defined('_MAGIC_QUOTES_GPC_') or define('_MAGIC_QUOTES_GPC_', get_magic_quotes_gpc()?true:false);
}
 
// DAO存储类型
define('DAO_TYPE_NONE',     0);
define('DAO_TYPE_BOTH',     1);
define('DAO_TYPE_MEMCACHE', 2);
define('DAO_TYPE_MYSQL',    3);
