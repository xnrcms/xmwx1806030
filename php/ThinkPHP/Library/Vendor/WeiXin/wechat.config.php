<?php
class WxPayConf_pub{
	const APPID 			= 'wx484feb08028f9fa0';
	const MCHID 			= '1510781501';
	const KEY 				= '3932fd3d6c049p801a0oe7b55ff60aaf';
	const APPSECRET 		= '403da36622b0e2364f5cd2b56c6ba639';
	const JS_API_CALL_URL 	= '';
	const SSLCERT_PATH 		= 'ThinkPHP/Library/Vendor/WeiXin/cert/apiclient_cert.pem';
	const SSLKEY_PATH 		= 'ThinkPHP/Library/Vendor/WeiXin/cert/apiclient_key.pem';
	const NOTIFY_URL 		= 'http://xmwx1806030.php.hzxmnet.com/Api/Pay/wxPaySuccess/';
	const CURL_TIMEOUT 		= 30;

	private $config			= array();
	public function __construct($rid){
		$this->rid			= $rid;
	}
	public function getConfig($key){
		$public_account		= $this->public_account();
		return $public_account[$key];
	}

	private function public_account(){
		$public_account =   M('public_account')->where(array('redid'=>$this->rid))->field(array('app_id','app_secret','wx_pay_key','wx_pay_mchid'))->find();
		return $public_account;
	}
}
?>