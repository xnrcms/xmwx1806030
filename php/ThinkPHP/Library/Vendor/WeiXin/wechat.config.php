<?php
class WxPayConf_pub{
	const APPID 			= 'wx343f4eb64cc0b950';
	const MCHID 			= '1394710702';
	const KEY 				= '3833fd350c0492801a0ee7b54ff60aaf';
	const APPSECRET 		= '1422747190097cf0e28aae9335fec0bf';
	const JS_API_CALL_URL 	= '';
	const SSLCERT_PATH 		= 'ThinkPHP/Library/Vendor/WeiXin/cert/apiclient_cert.pem';
	const SSLKEY_PATH 		= 'ThinkPHP/Library/Vendor/WeiXin/cert/apiclient_key.pem';
	const NOTIFY_URL 		= 'http://xmds1705077.php.hzxmnet.com/Api/Pay/wxPaySuccess/';
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