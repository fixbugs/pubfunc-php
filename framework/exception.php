<?php
/**
 * 异常处理
 *
 */
class Leb_Exception extends Exception
{
	/**
	 * 错误文件模板
	 *
	 * @var string
	 */
	protected $_exceptionFile = 'alert';

	/**
	 * 渲染器
	 *
	 * @var Leb_View
	 */
	protected $_viewer = null;

	/**
	 * 错误列表显示
	 *
	 * @param string $message
	 * @param code   $code
	 */
    public function __construct($message=0, $code=null, $previous=null)
    {
        parent::__construct($message, $code, $previous);
    }
}
