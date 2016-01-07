<?php
/**
 * 项目配置的基本信息
 */
return array(
	/**
	 * 是否打开调试模式(控制全局的调试开关)
	 */
	'debug'	=> true,

	/**
	 * 是否打开跟踪模式(控制消息是否在终端显示信息,当调试模式：debug_mode为firephp时此值必须为true)
	 */
	'app_debug'	=>	true,

	/**
	 * 系统部署环境,true为开发环境，false为应用环境(只是为了控制错误的显示级别及是否显示错误)
	 */
	'is_dev'	=>	true,

	/**
	 * 调试模式,可以为"page", 'firephp'
	 */
	'debug_mode'	=>	'firephp',
);