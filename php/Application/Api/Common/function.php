<?php
/**
 * 后台公共文件
 * 主要定义后台公共函数库
 */
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

/* function sendcode($moblie, $code){
	vendor('SMS.JSMS');
	$appKey 		= '62fc121a39b02ffc91c3156f';
	$masterSecret 	= '41c99023e209ef2adffa5653';
	$JSMS 			= new \JSMS($appKey,$masterSecret,true);
	$temp_id 		= 1;
	$temp_para  	= array('code'=>$code);
	$response 		= $JSMS->sendMessage($moblie, $temp_id, $temp_para);
	return true;
} */

function get_order_by_arr()
{
	return array(
	array('id'=>'1','name'=>'浏览最多'),
	array('id'=>'2','name'=>'收藏最多'),
	array('id'=>'3','name'=>'评价最高'));
}
function get_order_by_field($k,$pex='')
{
	$orderby	= array(
		'1'=>$pex.'.views desc',
		'2'=>$pex.'.collent_num desc',
		'3'=>$pex.'.commentscore desc');
	return $orderby[$k];
}
function get_score($mid)
{
	if($mid <= 0) return 0;
	$score		= M('member')->where(array('uid'=>$mid))->getField('commentscore');
	return $score;
}
//字段字符串处理
function get_fields_string($fields,$prefix=''){
	if ($prefix != ''){
		foreach ($fields as $key=>$val){
			$fields[$key] = $prefix.'.'.$val;
		}
	}
	return is_array($fields) ? implode(',', $fields) : $fields;
}
?>