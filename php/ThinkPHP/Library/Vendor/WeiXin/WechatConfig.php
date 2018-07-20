<?php
namespace Vendor\WeiXin;
class WechatConfig{
//	const APPID 			= 'wxb50d54232847a2fa';
//	const MCHID 			= '1314461601';
//	const KEY 				= '3833fd350c0492801a0ee7b54ff60aaf';
//	const APPSECRET 		= '116547db9dfc85dd6f1224bd3e0b0950';
//	const JS_API_CALL_URL 	= '';
//	const SSLCERT_PATH 		= 'ThinkPHP/Library/Vendor/WeiXin/cert/apiclient_cert.pem';
//	const SSLKEY_PATH 		= 'ThinkPHP/Library/Vendor/WeiXin/cert/apiclient_key.pem';
//	const NOTIFY_URL 		= 'http://mz.gochehui.com/Pay/wxPaySuccess/';
//	const CURL_TIMEOUT 		= 30;
	
	public function __construct($rid){
		$this->Rid			= $rid;
	}
	public function setConfig(){
		$public_account		= M('public_account')->where(array('redid'=>$this->Rid))->field(array('app_id','app_secret','wx_pay_key','wx_pay_mchid','wx_pay_sslcert_path','wx_pay_sslkey_path'))->find();
		C('APPID',$public_account['app_id']);
		C('MCHID',$public_account['wx_pay_mchid']);
		C('KEY',$public_account['wx_pay_key']);
		C('APPSECRET',$public_account['app_secret']);
		C('JS_API_CALL_URL','');
		C('SSLCERT_PATH',trim($public_account['wx_pay_sslcert_path'],'/'));
		C('SSLKEY_PATH',trim($public_account['wx_pay_sslkey_path'],'/'));
		C('NOTIFY_URL','http://wx.juyi99.cn/Home/Pay/wxPaySuccess/Rid/'.$this->Rid);
		C('CURL_TIMEOUT',30);
	}
}
?>