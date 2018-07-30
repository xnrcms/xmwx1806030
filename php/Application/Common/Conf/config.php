<?php
/**
 * 系统配文件
 * 所有系统级别的配置
 */
return array(
    /* 模块相关配置 */
    'AUTOLOAD_NAMESPACE' => array('Addons' => XNRCMS_ADDON_PATH), //扩展模块列表
    'DEFAULT_MODULE'     => 'Admin',
    'MODULE_DENY_LIST'   => array('Common', 'User'),

    /* 系统数据加密设置 */
    'DATA_AUTH_KEY' => 'k+_b}yC2Hx~:uZ/O=a9g-0{6^B|LhfwFlG@I?1MY', //默认数据加密KEY
	'LOAD_EXT_CONFIG' => 'db,alipay',

    /* 调试配置 */
    'SHOW_PAGE_TRACE' => false,

    /* 用户相关设置 */
    'USER_MAX_CACHE'     => 1000, //最大缓存用户数
    'USER_ADMINISTRATOR' => 1, //管理员用户ID

    /* URL配置 */
    'URL_CASE_INSENSITIVE' => true, //默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL'            => 2, //URL模式
    'VAR_URL_PARAMS'       => '', // PATHINFO URL参数变量
    'URL_PATHINFO_DEPR'    => '/', //PATHINFO URL分割符

    /* 全局过滤配置 */
    'DEFAULT_FILTER' => '', //全局过滤函数

    /* 文档模型配置 (文档模型核心配置，请勿更改) */
    'DOCUMENT_MODEL_TYPE' => array(2 => '主题', 1 => '目录', 3 => '段落'),
	'XNRCMS'=>'利客购物',
	'XNRCMS_VAR'=>'1.0',

	'LANG_SWITCH_ON' 	=> true,   // 开启语言包功能
	'LANG_AUTO_DETECT' 	=> true, // 自动侦测语言 开启多语言功能后有效
    'LANG_LIST'        	=> 'zh-cn', // 允许切换的语言列表 用逗号分隔
    'VAR_LANGUAGE'     	=> 'l', // 默认语言切换变量

    'MD5_KEYS'				=>	'tcw123456',
    'API_SMS_SN'			=>	'SDK-MOV-010-00345',
    'API_SMS_PASS'			=>	'869042',
		
	'CURRENCY_VALIDITY'		=> '7776000',	//聚蚁币有效期
	
	'UPLOAD_QINIU_CONFIG'=>array(
			'accessKey'=>'Ye8G53HCMWCK3KfsRNqDEBdCPLRPJkxFkI9v8zmZ',
			'secrectKey'=>'2TyDfNPbBlJfCmjEZm0OHhuP0vUmzxyE0z0ADr9w',
			'bucket'=>'jysc',
			'domain'=>'otccrtuba.bkt.clouddn.com',
			'timeout'=>3600,
	),
		
	//有关OSS
	'OSSCONFIG'=>array(
		'ACCESSKEYID' => 'LTAIyHy4RQxliMbW',
		'ACCESSKEYSECRET'=>'Xr6PC1dlnmS45oHT7HeAxag1JcEJcO',
		'ENDPOINT' =>'oss-cn-beijing.aliyuncs.com',
		'BUCKET' => 'lkgw',
		'HOST' => 'lkgw.oss-cn-beijing.aliyuncs.com'
	),
		
	//聚合银行卡key
	'JH_BANK_KEY' => 'a228b6106a213c866b94b01bc4e277e8',
		
	//快递鸟
	'KUAIDINIAO'=>array(
			'EBusinessID' 	=> '1350216',
			'AppKey'		=>'a48fe1e7-942a-47e2-8203-a5c122c3a3a6',
			'ReqURL' 		=>'http://api.kdniao.cc/api/dist'
	),
		
	//公众号
	/* 'GZH'=>array(
			'APPID' 		=> 'wx72b4e89421b13340',
			'APPSECRET'		=> 'dfbb26ca885383ec407e4255ca6ecc98',
	), */
	'GZH'=>array(
			'APPID' 		=> 'wx2c05d2312609a170',
			'APPSECRET'		=> 'ed8947c764fd9ae9b1a7c1572827e4a3',
	),
);