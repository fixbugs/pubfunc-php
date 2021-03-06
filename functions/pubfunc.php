<?php

function pr($str){
    print_r("---------");
    print_r($str);
    print_r("---------");
}
/**
 * 循环创建目录
 *
 * @param string $dir
 * @param int $mode
 * @return boolean
 */
function mk_dir($dir, $mode = 0755)
{
    if (is_dir($dir) || @mkdir($dir,$mode))
    {
        return true;
    }
    if (!mk_dir(dirname($dir),$mode)) {
        return false;
    }
    return @mkdir($dir,$mode);
}

/**
 * 递归将特殊字符为HTML字符编码
 *
 * @param array|string  $data
 * @return array|string
 */
function dhtmlspecialchars($data)
{
    if (is_array($data)) {
    	foreach ($data as $key => $value) {
    	    $data[$key] = dhtmlspecialchars($value);
    	}
    } else {
        $data = htmlspecialchars($data);
    }
    return $data;
}

/**
* @去除XSS（跨站脚本攻击）的函数
* @author By qiqing
* @param	string	 $val 字符串参数，可能包含恶意的脚本代码如<script language="javascript">alert("hello world");</script>
* @return 	string	 处理后的字符串
**/
function removeXss($val){
	// remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
	// this prevents some character re-spacing such as <java\0script>
	// note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
	$val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);

	// straight replacements, the user should never need these since they're normal characters
	// this prevents like <IMG SRC=@avascript:alert('XSS')>
	$search = 'abcdefghijklmnopqrstuvwxyz';
	$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$search .= '1234567890!@#$%^&*()';
	$search .= '~`";:?+/={}[]-_|\'\\';
	for ($i=0; $i<strlen($search); $i++){
		// ;? matches the ;, which is optional
		// 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
		// @ @ search for the hex values
		$val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
		// @ @ 0{0,7} matches '0' zero to seven times
		$val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
	}

	// now the only remaining whitespace attacks are \t, \n, and \r
	$ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
	$ra2 = array('on', 'onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
	$ra = array_merge($ra1, $ra2);

	$found = true; // keep replacing as long as the previous round replaced something
	while($found == true){
		$val_before = $val;
		for ($i = 0; $i < sizeof($ra); $i++) {
			$pattern = '/';
			for ($j = 0; $j < strlen($ra[$i]); $j++) {
				if($j > 0){
					$pattern .= '(';
					$pattern .= '(&#[xX]0{0,8}([9ab]);)';
					$pattern .= '|';
					$pattern .= '|(&#0{0,8}([9|10|13]);)';
					$pattern .= ')*';
				}
				$pattern .= $ra[$i][$j];
			}
			$pattern .= '/i';
			$replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
			$val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
			if ($val_before == $val) {
				// no replacements were made, so exit the loop
				$found = false;
			}
		}
	}
	return $val;
}

/**
 * 递归将HTML字符编码还原
 *
 * @param array|string $data
 * @return array|string
 */
function dhtmlspecialchars_decode($data)
{
    if (is_array($data)) {
    	foreach ($data as $key => $value) {
    	    $data[$key] = dhtmlspecialchars_decode($value);
    	}
    } else {
        $data = htmlspecialchars_decode($data);
    }
    return $data;
}

/**
 * 获取客户端IP, 参考zend frmaework
 *
 * @param  boolean $checkProxy  是否检查代理
 * @return string
 */
function getClientIp($checkProxy = true)
{
    $ip = '127.0.0.1';
    if($checkProxy && isset($_SERVER['HTTP_CLIENT_IP']))
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    elseif($checkProxy && isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    elseif(!empty($_SERVER['REMOTE_ADDR']))
        $ip = $_SERVER['REMOTE_ADDR'];

    return $ip;
}

/**
 * 注册全局环境变量
 * @param string	$key	变量名
 * @param mixed		$value	变量值
 * @param string	$type	变量的命名空间
 * @return void
 */
function add_gvar($key, $value, $type = ''){
    if(empty($key)){
        return false;
    }else{
        if(!empty($type)){
            $GLOBALS[$type][$key] = $value;
        } else {
            $GLOBALS[$key] = $value;
        }
        return true;
    }
}

/**
 * 获取全局环境变量
 * @param string $key	变量名
 * @param string $type	变量所在的命名空间
 * @return	mixed
 */
function get_gvar($key, $type=''){
    if(empty($key)){
        return false;
    }else{
        if(!empty($type)){
            return isset($GLOBALS[$type][$key]) ? $GLOBALS[$type][$key] : false;
        } else {
            return isset($GLOBALS[$key]) ? $GLOBALS[$key] : false;
        }
    }
}

/**
 * 获取网址域名
 *
 * @param string $url
 * @return mixed string/bool
 */
function getUrlDomain($url){
    if(preg_match('/^(https?:\/\/)?([a-z0-9.-]+)(\/.*)?$/i', $url,$matches)){
        return $matches[2];
    }
    return false;
}

/**
 * 检出输入中是否有空格,有则返回true，无则返回false
 * @param  string $value [description]
 * @return bool
 */
function spaceCheck($value){
    $trim_value = trim($value);
    $pos_ret = strpos($trim_value, " ");
    if(!($pos_ret === false)){
        return true;
    }
    if(strlen($trim_value) != strlen($value)){
        return true;
    }
    return false;
}

/**
 * 判断字符串前缀
 *
 * @param $str 需要判断的字符串
 * @param $needle 前缀
 * @return bool
 */
function startWith($str,$needle){
    return strpos($str,$needle) === 0;
}

/**
 * 判断字符串后缀
 *
 * @param $str 需要判断的字符串
 * @param $needle 后缀
 * @return bool
 */
function endWith($str,$needle){
    $length = strlen($needle);
    if($length == 0){
        return true;
    }
    return (substr($str,-$length) === $needle);
}

/**
 * 字符串命名风格转换
 * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
 * @param string $name 字符串
 * @param integer $type 转换类型
 * @return string
 */
function parseName($name, $type=0) {
    if ($type) {
        return ucfirst(preg_replace_callback('/_([a-zA-Z])/', function($match){return strtoupper($match[1]);}, $name));
    } else {
        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }
}

/**
 * 校验正常输入，去除非法输入，返回true或false
 *
 * @param string $value 需要校验的输入字符串
 * @return bool
 */
function checkNormalInput($value){
    return preg_match( "/^[a-zA-Z0-9~!@#$%^&*()_ （）{}【】、|，。 ~！：；;\.\x80-\xff]+/", $value, $match) && ($match[0] == $value);
}