<?php
namespace Vendor\WeiXin;
class WeixinHelper{
	var $AppId;
	var $AppSecret;
	var $Token;
	var $JsTicket;
	public function GetToken(){
		$PostDate=array(
			'grant_type' => 'client_credential',
			'appid' => $this->AppId,
			'secret' => $this->AppSecret
		);
		$BackCall=$this->CurlHttp('https://api.weixin.qq.com/cgi-bin/token',$PostDate,'DELETE');
		if(empty($BackCall)){
			return 'token调用失败';
		}
		
		$GetToken=json_decode($BackCall);
		// var_dump($GetToken);
		// exit;
		if(!empty($GetToken->errcode)){
			return $GetToken->errcode.':'.$GetToken->errmsg;
		}
		$this->Token	= $GetToken->access_token;
		return $GetToken;
	}
	public function GetJsApiTicket(){
		$BackCall=$this->CurlHttp('https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$this->Token.'&type=jsapi','','DELETE');
		if(empty($BackCall)){
			return 'jsapi调用失败';
		}
		$JsApiTicket=json_decode($BackCall);
		$this->JsTicket=$JsApiTicket->ticket;
		return $JsApiTicket;
	}
	public function createNonceStr($length = 16) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str = "";
		for ($i = 0; $i < $length; $i++) {
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}
	public function UserInfo($openid){
		$BackCall=$this->CurlHttp('https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->Token.'&openid='.$openid,'','DELETE',array('Content-Type:application/json;charset=utf-8'));
		if(empty($BackCall)){
			return 'user调用失败';
		}
		$UserInfo=json_decode($BackCall);
		if(!empty($UserInfo->errcode)){
			return $UserInfo->errcode.':'.$UserInfo->errmsg;
		}
		return $UserInfo;
	}
	public function GetQrcode($openid){
		$PostDate='{"action_name":"QR_LIMIT_SCENE","action_info":{"scene":{"scene_id":'.$openid.'}}}';
		$BackCall=$this->CurlHttp('https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$this->Token,$PostDate,'POST',array('Content-Type:application/json;charset=utf-8'));
		if(empty($BackCall)){
			$this->SendText($openid,'qrcode调用失败');
		}
		$GetQrcode=json_decode($BackCall);
		if(!empty($GetQrcode->errcode)){
			$this->SendText($openid,$GetQrcode->errcode.':'.$GetQrcode->errmsg);
		}
		return $GetQrcode->ticket;
	}
	public function GetWxQrcode($type=0,$scene,$validity_time=2592000){
		if($type == 1){
			if (is_string($scene)){
				$PostDate='{"action_name":"QR_LIMIT_SCENE","action_info":{"scene":{"scene_str":'.trim($scene).'}}}';
			}elseif (is_numeric($scene)){
				$PostDate='{"action_name":"QR_LIMIT_SCENE","action_info":{"scene":{"scene_id":'.intval($scene).'}}}';
			}
		}else{
			$PostDate='{"expire_seconds":'.$validity_time.',"action_name":"QR_SCENE","action_info":{"scene":{"scene_id":'.intval($scene).'}}}';
		}
		$BackCall=$this->CurlHttp('https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$this->Token,$PostDate,'POST',array('Content-Type:application/json;charset=utf-8'));
		if(empty($BackCall)){
			return '调用qrcode失败';
		}
		$GetQrcode=json_decode($BackCall);
		if(!empty($GetQrcode->errcode)){
			return $GetQrcode->errcode.':'.$GetQrcode->errmsg;
		}
		return $GetQrcode->ticket;
	}
	public function MenuCreate($PostDate){
		$BackCall=$this->CurlHttp('https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->Token,$PostDate,'POST',array('Content-Type:application/json;charset=utf-8'));
		if(empty($BackCall)){
			return '调用失败';
		}
		$GetReturn=json_decode($BackCall);
		if(!empty($GetReturn->errcode)){
			return $GetReturn->errcode.':'.$GetReturn->errmsg;
		}
		return $GetReturn->errcode;
	}
	public function GetFile($mediaid){
		$GetReturn=$this->CurlHttp('http://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$this->Token.'&media_id='.$mediaid,'','DELETE',array('Content-Type:application/json;charset=utf-8'));
		if(empty($GetReturn)){
			return '调用失败';
		}
		return $GetReturn;
	}
	public function UploadFile($type,$PostDate){
		if($type=='image'){
			$file=DATA_PATH.'/tmp/'.NOW_TIME.'.jpg';
		}elseif($type=='voice'){
			$file=DATA_PATH.'/tmp/'.NOW_TIME.'.mp3';
		}elseif($type=='video'){
			$file=DATA_PATH.'/tmp/'.NOW_TIME.'.mp4';
		}else{
			$file=DATA_PATH.'/tmp/'.NOW_TIME.'.tmp';
		}
		IO::GetImagePut($file,$PostDate);
		$BackCall=$this->CurlHttp('http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token='.$this->Token.'&type='.$type,$file,'FILE');
		unlink($file);
		if(empty($BackCall)){
			return '调用失败';
		}
		$GetReturn=json_decode($BackCall);
		return $GetReturn;
	}
	public function SendMesg($PostDate){
		$BackCall=$this->CurlHttp('https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$this->Token,$PostDate,'POST');
		if(empty($BackCall)){
			return '调用失败';
		}
		$GetReturn=json_decode($BackCall);
		if(!empty($GetReturn->errcode)){
			//die($GetReturn->errcode.':'.$GetReturn->errmsg);
			return true;
		}
		return true;
	}
	public function CurlHttp($url,$body='',$method='DELETE',$headers=array()){
		$httpinfo=array();
		$ci=curl_init();
		curl_setopt($ci,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_0);
		curl_setopt($ci,CURLOPT_SSLVERSION,CURL_SSLVERSION_TLSv1);
		curl_setopt($ci,CURLOPT_USERAGENT,'dioig.com');
		curl_setopt($ci,CURLOPT_CONNECTTIMEOUT,30);
		curl_setopt($ci,CURLOPT_TIMEOUT,30);
		curl_setopt($ci,CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($ci,CURLOPT_ENCODING,'');
		curl_setopt($ci,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ci,CURLOPT_HEADER,FALSE);
		switch($method){
			case 'POST':
				curl_setopt($ci,CURLOPT_POST,TRUE);
				if(!empty($body)){
					curl_setopt($ci,CURLOPT_POSTFIELDS,$body);
				}
				break;
			case 'FILE':
				curl_setopt($ci,CURLOPT_POST,TRUE);
				curl_setopt($ci,CURLOPT_POSTFIELDS,
				array('media'=>'@'.realpath($body))
				);
				break;
			case 'DELETE':
				curl_setopt($ci,CURLOPT_CUSTOMREQUEST,'DELETE');
				if(!empty($body)){
					$url=$url.'?'.str_replace('amp;', '', http_build_query($body));
				}
		}
		curl_setopt($ci,CURLOPT_URL,$url);
		curl_setopt($ci,CURLOPT_HTTPHEADER,$headers);
		curl_setopt($ci,CURLINFO_HEADER_OUT,TRUE);
		$response=curl_exec($ci);
		$httpcode=curl_getinfo($ci,CURLINFO_HTTP_CODE);
		$httpinfo=array_merge($httpinfo,curl_getinfo($ci));
		curl_close($ci);
		return $response;
	}
	public function SendText($openid,$desc){
		$PostDate=array(
			'touser'=>'{OPENID}',
			'msgtype'=>'text',
			'text'=>array('content'=>'{DESC}'),
		);
		$replace=array(
			'/\{OPENID\}/si'=>$openid,
			'/\{DESC\}/si'=>$desc
		);
		$ResultStr=$this->SendMesg(preg_replace(array_keys($replace),array_values($replace),json_encode($PostDate)));
		die($ResultStr);
	}
	public function SendImage($openid,$MediaId){
		$PostDate=array(
			'touser'=>'{OPENID}',
			'msgtype'=>'image',
			'image'=>array(
				'media_id'=>'{MediaId}',
		),
		);
		$replace=array(
			'/\{OPENID\}/si'=>$openid,
			'/\{MediaId\}/si'=>$MediaId
		);
		$ResultStr=$this->SendMesg(preg_replace(array_keys($replace),array_values($replace),json_encode($PostDate)));
		die($ResultStr);
	}
	public function SendVoice($openid,$MediaId){
		$PostDate=array(
			'touser'=>'{OPENID}',
			'msgtype'=>'voice',
			'voice'=>array(
				'media_id'=>'{MediaId}',
		),
		);
		$replace=array(
			'/\{OPENID\}/si'=>$openid,
			'/\{MediaId\}/si'=>$MediaId
		);
		$ResultStr=$this->SendMesg(preg_replace(array_keys($replace),array_values($replace),json_encode($PostDate)));
		die($ResultStr);
	}
	public function SendVideo($openid,$Title,$Description,$MediaId){
		$PostDate=array(
			'touser'=>'{OPENID}',
			'msgtype'=>'video',
			'video'=>array(
				'media_id'=>'{MediaId}',
				'title'=>'{Title}',
				'description'=>'{Description}',
		),
		);
		$replace=array(
			'/\{OPENID\}/si'=>$openid,
			'/\{Title\}/si'=>$Title,
			'/\{Description\}/si'=>$Description,
			'/\{MediaId\}/si'=>$MediaId
		);
		$ResultStr=$this->SendMesg(preg_replace(array_keys($replace),array_values($replace),json_encode($PostDate)));
		die($ResultStr);
	}
	public function SendMusic($openid,$Title,$Description,$MusicUrl,$HQMusicUrl,$ThumbMediaId){
		$PostDate=array(
			'touser'=>'{OPENID}',
			'msgtype'=>'music',
			'music'=>array(
				'title'=>'{Title}',
				'description'=>'{Description}',
				'musicurl'=>'{MusicUrl}',
				'hqmusicurl'=>'{HQMusicUrl}',
				'thumb_media_id'=>'{ThumbMediaId}'
				),
				);
				$replace=array(
			'/\{OPENID\}/si'=>$openid,
			'/\{Title\}/si'=>$Title,
			'/\{Description\}/si'=>$Description,
			'/\{MusicUrl\}/si'=>$MusicUrl,
			'/\{HQMusicUrl\}/si'=>$HQMusicUrl,
			'/\{ThumbMediaId\}/si'=>$ThumbMediaId
				);
				$ResultStr=$this->SendMesg(preg_replace(array_keys($replace),array_values($replace),json_encode($PostDate)));
				die($ResultStr);
	}
	public function SendNews($openid,$item){
		$PostDate='{"touser":"'.$openid.'","msgtype":"news","news":{"articles": [';
		$articles=array();
		foreach ($item AS $value){
			$articles[]='{"title":"'.addslashes($value['title']).'","description":"'.addslashes($value['description']).'","url":"'.addslashes($value['url']).'","picurl":"'.addslashes($value['picurl']).'"}';
		}
		$PostDate .= implode(',',$articles);
		$PostDate .= ']}}';
		$ResultStr=$this->SendMesg($PostDate);
		die($ResultStr);
	}
	//生成签名
	function MakeSign($bizObj)
	{
		//参数小写
		foreach ($bizObj as $k => $v){
			$bizParameters[strtolower($k)] = $v;
		}
		//字典序排序
		ksort($bizParameters);
		//URL键值对拼成字符串
		$buff = "";
		foreach ($bizParameters as $k => $v){
			$buff .= $k."=".$v."&";
		}
		//去掉最后一个多余的&
		$buff2 = substr($buff, 0, strlen($buff) - 1);
		//sha1签名
		return sha1($buff2);
	}
	//生成OAuth2的URL
	public function oauth2_authorize($redirect_url, $scope, $state = NULL)
	{
		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->AppId."&redirect_uri=".$redirect_url."&response_type=code&scope=".$scope."&state=".$state."#wechat_redirect";
		return $url;
	}
	//获取微信授权TOKEN
	public function GetWxAuthToken($code){
		$GetReturn=json_decode($this->CurlHttp('https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->AppId.'&secret='.$this->AppSecret.'&code='.$code.'&grant_type=authorization_code','','GET',array('Content-Type:application/json;charset=utf-8')));
		if(!empty($GetReturn->openid)){
			return $GetReturn;
		}
		return false;
	}
	public function GetWxAuthUser($authInfo){
		if($authInfo->scope=='snsapi_userinfo'){
			$UserInfo	= json_decode($this->CurlHttp('https://api.weixin.qq.com/sns/userinfo?access_token='.$authInfo->access_token.'&openid='.$authInfo->openid.'&lang=zh_CN','','GET',array('Content-Type:application/json;charset=utf-8')));
		}else{
			$UserInfo	= $authInfo;
		}
		return $UserInfo;
	}
}