<?php
/**
 * @category   Leb
 */

defined('__FRM_BEGIN__') or define('__FRM_BEGIN__', microtime(true));
include(dirname(__FILE__).DIRECTORY_SEPARATOR.'global.php');
//检查PHP版本
if(version_compare(PHP_VERSION, '5.3.0', '<')){
	die('require PHP > 5.3.0 !');
}

if(defined('_DEBUG_') && _DEBUG_){
    //检测系统是否支持64位
    //if(PHP_INT_MAX == 2147483647)
    //    trigger_error('Non-64 system does not work properly', E_USER_WARNING);

    //检测Redis
    //if(!extension_loaded('redis')){
	//	die('require redis extension !');
	//}

    //监测CouchBase
    //if(!extension_loaded('couchbase')){
	//	die('require couchbase extension !');
	//}

    //检测PDO
    if(!extension_loaded('pdo_mysql')){
		die('require PDO MySQL extension');
	}

    //检测反射类
    if(!class_exists('Reflection',false)){
		die('require Reflection extension');
	}

    //检测SPL
    if(!extension_loaded('SPL')){
		die('require SPL extension');
	}
}

/**
 * 框架基础类
 */
class Leb_Appbase
{
    public $charset = 'UTF8';
    public $name = 'Slae Framework';
    protected $_hinstance = array();
    protected static $_appId    = null;
    protected static $_appKey   = null;
    protected static $_appInfo  = null;
    protected static $_imports  = array();
    protected static $_corePath = null;

	/**
	 * 核心对象(名称-类名)映射关系
	 * @var array
	 */
    private $_coreObj = array(
        'request' => 'Leb_Request',
        'router'  => 'Leb_Router',
        //'gearman' => 'plugin_gearman',
        //'comdb'   => 'Leb_Dao_Comdb',
        'log'     => 'Leb_Log',
    );


	/**
	 * 构造函数
	 * @return void
	 */
    public function __construct()
    {
        //@ob_start();
        register_shutdown_function(array($this, 'shutdown'));
        set_exception_handler(array($this,'handleException'));
        set_error_handler(array($this,'handleError'), ini_get('error_reporting'));
        assert_options(ASSERT_ACTIVE,   true);
        assert_options(ASSERT_BAIL,     true);
        assert_options(ASSERT_WARNING,  true);
        assert_options(ASSERT_CALLBACK, array($this, 'handleAssert'));
        !self::$_corePath && self::$_corePath = include(dirname(__FILE__).DIRECTORY_SEPARATOR.'classes.php');
        self::setAutoLoad();
    }

    /**
	 * 获取全局对象(获取顺序:
	 *  1:类成员实例窗口
	 *  2:核心对象映射关系
	 *  3:全局对象映射关系
	 *  4:数组成员对象
	 * )
	 * @param string|array  $name  类名或者类的映射名称 
	 * @return Object|Exception   实例化的类对象或者异常
     */
    public function __get($name)
    {
        $isStr = is_string($name);
        if($isStr && isset($this->_hinstance[$name])){
            return $this->_hinstance[$name];
		}elseif($isStr && isset($this->_coreObj[$name])){
            return $this->_hinstance[$name] = $this->getComponent($this->_coreObj[$name]);
		}elseif($isStr && isset(self::$_corePath[$name])){
            return $this->getComponent($name);
		}elseif(is_array($name) && isset($name['class']) && $class=$name['class'] && isset(self::$_corePath[$class])){
			return $this->getComponent($class, $name);
		}else{
			throw new Exception("Can't get component:".$name);
		}
    }

    /**
     * 实例化对象
	 * @param  string  $class 类名称
	 * @param  array   $param 实例化类对象参数
	 * @return Object|null  实例化后的类对象,如果找不到此类,则返回null
     */
    public function getComponent($class, $param=array())
    {
        $obj = null;
		$is_import = self::import($class);
        if($is_import && (class_exists($class, false) || interface_exists($class, false))){
            $m = new ReflectionClass($class);
            if($m && $m->hasMethod('getInstance') && $m->getMethod('getInstance')->isPublic()){
                if($param && $m->getNumberOfParameters()){
                    $obj = call_user_func_array(array($class, 'getInstance'), $param);
				}else{
					$obj = call_user_func(array($class, 'getInstance'));
				}
            }elseif($m){
                $obj = $param ? new $class($param) : new $class();
            }

            if($obj && $m->hasMethod('init') && $m->getMethod('init')->isPublic()){
                $obj->init();
            }

            $this->_hinstance[$class] = $obj;
        }

        return $obj;
    }

    /**
     * 返回框架版本号
     */
    public function getVer()
    {
        return '1.0.0';
    }

    /**
     * 加载指定Model类
     * 说明：未指定app优先加载当前应用Model
     */
    public function model($name, $app='')
    {
		if(!$app){
			$app = $this->getApplicationId();
		}
        return Leb_Model::model($name, $app);
    }

    /**
     * 加载表单对象
     * 说明：未指定app优先加载当前应用表单
     */
    public function form($name, $app='')
    {
		if(!$app){
			$app = $this->getApplicationId();
		}
        return Leb_Form::form($name, $app);
    }

    /**
     * 异常回调处理
     */
    public function handleException($exception)
    {
        restore_error_handler();
        restore_exception_handler();
        try{
            $file = $exception->getFile();
            $message = $exception->getMessage();
            $line = $exception->getLine();
            $code = $exception->getCode();
            !$code && $code = E_ERROR;
            $er = $this->errorInfo($code, $message, $file, $line, 0, $exception);
            $this->showError($code, $message, $file, $line, $er, $exception);
        }catch(Exception $e){
            $this->showException($e);
        }
    }

    /**
     * 断言回调处理
     */
    public function handleAssert($file, $line, $message)
    {
        try
        {
            $code = E_WARNING;
            $er = $this->errorInfo($code, $message, $file, $line);
            $this->showError($code, $message, $file, $line, $er, null);
        }
        catch(Exception $e)
        {
            $this->showException($e);
        }
    }

    /**
     * 错误回调处理
     */
    public function handleError($code, $message, $file, $line)
    {
        restore_error_handler();
        restore_exception_handler();
        try
        {
            $er = $this->errorInfo($code, $message, $file, $line);
            $this->showError($code, $message, $file, $line, $er, null);
        }
        catch(Exception $e)
        {
            $this->showException($e);
        }
    }

    /**
     * 显示错误信息
     */
    private function showError($code, $message, $file, $line, $error, $exception=null)
    {
        if(!_DEBUG_){
			return;
		}
        if (ob_get_contents()){
	        ob_clean();
        }
        $now = date('Y-m-d H:i:s');
        $title = $exception ? get_class($exception) : "PHP Error [{$code}]";
        extract($error);
        require(dirname(__FILE__).(_CLI_ ? '/view/command.php':'/view/web.php'));
        echo ob_get_clean();
        exit(1);
    }

    /**
     * 获取错误信息
     */
    public function errorInfo($code, $message, $file, $line, $skip=1, $exception=null)
    {
        $array = array();
        $array['exectime'] = microtime(true)-__FRM_BEGIN__;
        $array['error']['file'] = $file;
        $array['error']['line'] = $line;
        $array['error']['message'] = $message;
        $array['error']['code'] = $code;
        $array['error']['source'] = $this->getSource($file, $line);
        $array['ver'] = $this->getVer();
        $array['time'] = date('Y-m-d H:i:s');
        $array['server']= isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown';
        $array['phpver']=PHP_VERSION;
        $array['mmem'] = memory_get_peak_usage(true);
        $array['umem'] = memory_get_usage(true) - _FRM_BMEMORY_;
        $array['addr'] = $_SERVER['SERVER_ADDR'];
        $array['from'] = $this->request->getClientIp();
        $array['host'] = $_SERVER['HTTP_HOST'];
        $array['stack'] = array();
        $trace = $exception ? $exception->getTrace() : debug_backtrace();
        if(count($trace) > $skip){
			$trace = array_slice($trace, $skip);
		}
        foreach($trace as $key => $item)
        {
            !isset($item['file']) && $item['file']='unknown';
            !isset($item['line']) && $item['line']=0;
            !isset($item['function']) && $item['function']='unknown';
            !isset($item['type']) && $item['type'] = '';
            !isset($item['class']) && $item['class'] = '';
            !isset($item['args']) && $item['args'] = array();
            if($item['source'] = $this->getSource($item['file'], $item['line']))
                $array['stack'][] = $item;
        }

        return $array;
    }

    /**
     * 获取源代码
     */
    public function getSource($file, $lineNumber, $padding=3)
    {
        $source = array();
		if(!$file || !is_readable($file) || !$fp = fopen($file, 'r')){
			return $source;
		}

        $bline = $lineNumber - $padding;
        $eline = $lineNumber + $padding;
        $line = 0;
        while(($row = fgets($fp)))
        {
            if(++$line > $eline){
				break;
			}
            if($line < $bline){
				continue;
			}
            $row = htmlspecialchars($row, ENT_NOQUOTES);
            $source[$line] = $row;
        }
        fclose($fp);
        return $source;
    }

    /**
     * 注册结束脚本回调函数
	 * @param  无
	 * @return void 
     */
    public static function shutdown()
    {
        !defined('__FRM_ACTION_END__') && define('__FRM_ACTION_END__', microtime(true));
        $e = error_get_last();
        if(defined('__FRM_ACTION_END__ ') && defined('__FRM_ACTION_BEGIN__'))
            Slae::app()->log->info('Response:'.(__FRM_ACTION_END__ - __FRM_ACTION_BEGIN__), 'system');
        if(defined('__FRM_ACTION_BEGIN__') && defined('__FRM_BEGIN__'))
            Slae::app()->log->info('Framework:'.(__FRM_ACTION_BEGIN__ - __FRM_BEGIN__), 'system');

        //路由分发日志信息
        $route = new Leb_Log_Router();
        $route->renderLog();

        //无错误信息立即输出缓冲内容
        if(empty($e))
        {
            @flush();
            @ob_flush();
            @ob_end_flush();
            if(function_exists('fastcgi_finish_request'))
                fastcgi_finish_request();
        }

        Leb_Dao_Memcache::getInstance()->debugRtStats();
        if(XHPROF_ANALYSIS && _DEBUG_ && function_exists('xhprof_disable'))
        {
            if($data = xhprof_disable())
                Slae::app()->log->profile($data, 'system');
            //format
            //[ct] => 2        # number of calls to bar() from foo()
            //[wt] => 37       # time in bar() when called from foo()
            //[cpu]=> 0        # cpu time in bar() when called from foo()
            //[mu] => 2208     # change in PHP memory usage in bar() when called from foo()
            //[pmu]=> 0        # change in PHP peak memory usage in bar() when called from foo()
        }

        //捕捉错误信息
        if(!empty($e))
        {
            $code = $e['type'];
            $file = $e['file'];
            $line = $e['line'];
            $message = $e['message'];
            $er = Slae::app()->errorInfo($code, $message, $file, $line);
            if($file && false !== strpos($file, dirname(__FILE__)._DS_.'view'._DS_.'Smarty'))
                E_ERROR == $code && Slae::app()->showError($code, $message, $file, $line, $er, null);
            elseif($code & (E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_PARSE | E_RECOVERABLE_ERROR))
                Slae::app()->showError($code, $message, $file, $line, $er, null);
        }

        //根据配置把日志刷入日志系统
    }

    /**
     * 自动加载类
     * 这个函数不能抛出异常，否则无法充分利用SPL对autoload_functions()中的自动加载函数表的遍历功能
     * @param string $class      - The full class name of a Leb component.
     * @return boolean true 表示类成功加载，false 表示类没加载成功，需要继续其他的autoload函数
     * @throws no throws
     */
    public static function autoload($class)
    {
        if(class_exists($class, false) || interface_exists($class, false))
            return true;

        $file = '';
        if(isset(self::$_corePath[$class]))
            $file = dirname(__FILE__).self::$_corePath[$class];
        elseif(isset(self::$_corePath['Leb_'.$class]))
            $file = dirname(__FILE__).self::$_corePath['Leb_'.$class];
        elseif(false !== strpos($class, '\\'))
            $file = strtolower(str_replace('\\', _DS_, $class)).'.php';
        elseif($file = explode('_', $class))
        {
            if('Leb' == $file[0])
                array_shift($file);
            elseif('Smarty' == $file[0])
                return true;
            $file = implode(_DS_, $file).'.php';
            !file_exists($file) && $file = strtolower($file);
        }

        include_once($file);
        if(class_exists($class, false) || interface_exists($class, false))
            return class_exists($class, false) || interface_exists($class, false);
        elseif($obj = Leb_Model::import($class, Slae::app()->router->getApp(), true))
            return false != $obj;
        elseif($obj = Leb_Form::import($class, Slae::app()->router->getApp(), false))
            return false != $obj;
        else
            return false;
    }

    /**
     * 导入核心类文件
	 * @param string $class 类名称/类映射名称
	 * @return string|bool  文件存在则返回
     */
    public static function import($class)
    {
        if(isset(self::$_imports[$class])){
            return self::$_imports[$class];
		}elseif(class_exists($class, false) || interface_exists($class, false)){
            return self::$_imports[$class] = $class;
		}elseif(isset(self::$_corePath[$class]) && include_once(dirname(__FILE__).self::$_corePath[$class])){
            return self::$_imports[$class] = dirname(__FILE__).self::$_corePath[$class];
		}else{
			return false;
		}
    }

    /**
     * 自动注册 {@link autoload()} 用 spl_autoload() 方法自动实现
     * @return void
     * @throws Leb_Exception 如果spl_autoload()不存在，抛出本异常
     */
    public static function setAutoLoad()
    {
        if(!function_exists('spl_autoload_register')){
			throw new Exception('spl_autoload 不存在，可能是SPL库没有安装');
		}
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    /**
     * 返回应用对象ID
     */
    public function getApplicationId()
    {
        return $this->router->getApp();
    }

    /**
     * 返回控制器ID
     */
    public function getControllerId()
    {
        return $this->router->getController();
    }

    /**
     * 返回响应方法ID
     */
    public function getActionId()
    {
        return $this->router->getAction();
    }

    /**
     * 设置分配的AppId
     */
    public function setAppId($appid, $key, $host='127.0.0.1', $port=11211)
    {
        self::$_appId = $appid;
        self::$_appKey= $key;
        /*
        $cache = Leb_Dao_Memcache::getInstance();
        $info = $cache->get($appid);
        !$info && trigger_error('Cannot get app information');
        $this->setConfig($info);
         */
    }

    /**
     * 返回平台分配项目ID
     */
    public function getAppId()
    {
        return self::$_appId;
    }

    /**
     * 返回平台分配项目密钥
     */
    public function getAppKey()
    {
        return self::$_appKey;
    }

    /**
     * 设置配置信息
     */
    public function setConfig(array $config)
    {
        !$config && trigger_error('config can not be empty');
        self::$_appInfo = new Leb_Configure();
        foreach($config as $k => $v)
        {
            property_exists(self::$_appInfo, $k) && self::$_appInfo->$k = $v;
        }
    }

    /**
     * 返回Slae平台配置信息
     */
    public function getConfig()
    {
        return self::$_appInfo;
    }
}

/**
 * CLI模式应用类
 */
class Leb_CmdApp extends Leb_Appbase
{
    public function __construct()
    {
        parent::__construct();
    }
}

/**
 * Web模式应用类
 */
class Leb_WebApp extends Leb_Appbase
{
    private $_coreObj = array(
        'user'    => 'Leb_User',
        'session' => 'Leb_Session',
        'view'    => 'Leb_View',
        'cookie'  => 'Leb_Cookie',
    );

    public function __construct()
    {
        parent::__construct();
        header('Content-Type: text/html;charset=utf-8');
    }

    public function __get($name)
    {
        if(isset($this->_hinstance[$name]))
            return $this->_hinstance[$name];
        elseif(isset($this->_coreObj[$name]))
            return parent::__get($this->_coreObj[$name]);
        else
            return parent::__get($name);
    }
}

/**
 * Slae框架全局静态类
 */
class Slae
{
    protected static $_app   = null;

    /**
     * 返回全局对象
     */
    public static function app()
    {
        return self::$_app;
    }

    /**
     * 实例化全局应用对象
     */
    public static function createApp()
    {
        self::$_app   = _CLI_ ? new Leb_CmdApp() : new Leb_WebApp();
        //die("dd");
    }

    /**
     * 加载全局Model
     */
    public static function model($name, $app=false)
    {
        return Leb_Model::model($name);
    }

    /**
     * 加载全局Form
     */
    public static function form($name)
    {
        return Leb_Form::form($name);
    }

    /**
     * 实例化过滤条件对象实例
     */
    public static function criteria($attr=array(), $operator='=', $logic='AND', $parent=null)
    {
        return Leb_Model::criteria($attr, $operator, $logic, $parent);
    }

    /**
     * 静态魔术方法
     */
    public static function __callStatic($name, $args)
    {
        if(self::$_app->import($name))
            return self::$_app->getComponent($name, $args[0]);
        elseif($m = new ReflectionMethod('Leb_Util', $name))
            return call_user_func_array('Leb_Util::'.$name, $args);
        else
            throw new Exception("Can't find static method:".$name);
    }

    /**
     * 调试跟踪
     */
    public static function trace($message, $category='app')
    {
        self::app()->log->trace($message, $category);
    }

    /**
     * 记录日志
     */
    public static function log($message, $level='info', $category='app')
    {
        self::app()->log->log($message, $level, $category);
    }

    /**
     * 输出调用栈
     */
    public static function showDebugTrace()
    {
        $content = ob_get_clean();
        $trace = debug_backtrace();
        foreach($trace as $item)
        {
        }
        exit(0);
    }

    /**
     * 调用栈
     */
    private static function getBacktrace($level)
    {
        $message = '';
        $trace = debug_backtrace();
        $count = 0;
        foreach($trace as $item)
        {
            if(isset($item['file'], $item['line']) && strpos($item['file'], _FRAMEWORK_) !==0 )
            {
                $message .= "\nin ".$item['file'].' ('.$item['line'].')';
                if(++$count >= $level)
                    break;
            }
        }

        return $message;
    }
}

Slae::createApp();
