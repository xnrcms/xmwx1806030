<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

/**
 * 前台公共库文件
 * 主要定义前台公共函数库
 */

/**
 * 检测验证码
 * @param  integer $id 验证码ID
 * @return boolean     检测结果
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function check_verify($code, $id = 1){
	$verify = new \Think\Verify();
	return $verify->check($code, $id);
}

/**
 * 获取列表总行数
 * @param  string  $category 分类ID
 * @param  integer $status   数据状态
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_list_count($category, $status = 1){
    static $count;
    if(!isset($count[$category])){
        $count[$category] = D('Document')->listCount($category, $status);
    }
    return $count[$category];
}

/**
 * 获取段落总数
 * @param  string $id 文档ID
 * @return integer    段落总数
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_part_count($id){
    static $count;
    if(!isset($count[$id])){
        $count[$id] = D('Document')->partCount($id);
    }
    return $count[$id];
}

/**
 * 获取运费
 * @param unknown $addressId
 * @return number
 */
function get_shipping_fee($addressId){
	$address = M('address')->field('province')->where(array('id'=>$addressId))->find();
	if(!empty($address['province'])){
		$fare = M('fare')->field('fee')->where(array('area'=>array('like', '%'.$address['province'].'%')))->find();
		$fee = $fare['fee'];
	}
	if(empty($fee)){
		$fee = 0;
	}
	return $fee;
}

/**
 *短息发送
 */
function sendcode_($moblie,$content)
{
	$flag 				= 0;  //要post的数据
	$content 			= iconv( "UTF-8", "gb2312" ,$content);
	$argv['sn']			= C('API_SMS_SN');//序列号
	$argv['pwd']		= strtoupper(md5(C('API_SMS_SN').C('API_SMS_PASS')));//此处密码需要加密 加密方式为 md5(sn+password) 32位大写
	$argv['mobile']		= $moblie;//手机号 多个用英文的逗号隔开 post理论没有长度限制.推荐群发一次小于等于100
	$argv['content']	= $content;//短信内容
	$argv['ext']		= '';
	$argv['stime']		= '';//定时时间 格式为2011-6-29 11:09:21
	$argv['rrid']		= '';
	//构造要post的字符串
	foreach ($argv as $key=>$value){
		if ($flag!=0){
			$params .= "&";
			$flag = 1;
		}
		$params.= $key."="; $params.= urlencode($value);
		$flag = 1;
	}
	$length = strlen($params);
	//创建socket连接
	$fp = fsockopen("sdk.entinfo.cn",8060,$errno,$errstr,10) or exit($errstr."--->".$errno);
	//构造post请求的头
	$header = "POST /webservice.asmx/mt HTTP/1.1\r\n";
	$header .= "Host:sdk.entinfo.cn\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: ".$length."\r\n";
	$header .= "Connection: Close\r\n\r\n";
	//添加post的字符串
	$header .= $params."\r\n";
	//发送post的数据
	fputs($fp,$header);
	$inheader = 1;
	while (!feof($fp)) {
		$line = fgets($fp,1024); //去除请求包的头只显示页面的返回数据
		if ($inheader && ($line == "\n" || $line == "\r\n")) {
			$inheader = 0;
		}
		if ($inheader == 0) {
			// echo $line;
		}
	}
	$line=str_replace("<string xmlns=\"http://tempuri.org/\">","",$line);
	$line=str_replace("</string>","",$line);
	$result=explode("-",$line);
	if(count($result)>1){
		return false;
	}else{
		return true;
	}
}
?>