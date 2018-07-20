<?php
namespace Vendor\WeiXin;
class WebChatCallHelper{
	var $PostObj;
	public function valid($Token){
		$echoStr=$_GET['echostr'];
		// valid signature , option
		if($this->checkSignature($Token)){
			echo $echoStr;
		}else{
			echo '参数错误';
		}
		exit();
	}
	private function checkSignature($Token){
		$signature=$_GET['signature'];
		$timestamp=$_GET['timestamp'];
		$nonce=$_GET['nonce'];
		$tmpArr=array($Token,$timestamp,$nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr=implode($tmpArr);
		$tmpStr=sha1($tmpStr);
		if($tmpStr==$signature){
			return true;
		}else{
			return false;
		}
	}
	public function SetTrim($Str){
		//$arr=array(' '=>'|','　'=>'|','；'=>'|',','=>'|','，'=>'|','.'=>'|','。'=>'|','、'=>'|','\\'=>'|','/'=>'|',';'=>'|','?'=>'\?','!'=>'\!','$'=>'\$','*'=>'\*','.'=>'\.','('=>'\(',')'=>'\)','['=>'\[',']'=>'\]','{'=>'\{','}'=>'\}','\\'=>'\\\\');
		//$Str=strtr($Str,$arr);
		$Str=str_replace('\r','',$Str);
		$Str=str_replace('\n','',$Str);
		return trim(ltrim(rtrim($Str)));
	}
	public function SendText($Str){
		$TextTpl='<xml>';
		$TextTpl.='<ToUserName><![CDATA[%s]]></ToUserName>';
		$TextTpl.='<FromUserName><![CDATA[%s]]></FromUserName>';
		$TextTpl.='<CreateTime>%s</CreateTime>';
		$TextTpl.='<MsgType><![CDATA[text]]></MsgType>';
		$TextTpl.='<Content><![CDATA[%s]]></Content>';
		$TextTpl.='</xml>';
		$ResultStr=sprintf($TextTpl,$this->PostObj->FromUserName,$this->PostObj->ToUserName,NOW_TIME,$Str);
		die($ResultStr);
	}
	public function SendImage($MediaId){
		$TextTpl='<xml>';
		$TextTpl.='<ToUserName><![CDATA[%s]]></ToUserName>';
		$TextTpl.='<FromUserName><![CDATA[%s]]></FromUserName>';
		$TextTpl.='<CreateTime>%s</CreateTime>';
		$TextTpl.='<MsgType><![CDATA[image]]></MsgType>';
		$TextTpl.='<Image><MediaId><![CDATA[%s]]></MediaId></Image>';
		$TextTpl.='</xml>';
		$ResultStr=sprintf($TextTpl,$this->PostObj->FromUserName,$this->PostObj->ToUserName,NOW_TIME,$MediaId);
		die($ResultStr);
	}
	public function SendVoice($MediaId){
		$TextTpl='<xml>';
		$TextTpl.='<ToUserName><![CDATA[%s]]></ToUserName>';
		$TextTpl.='<FromUserName><![CDATA[%s]]></FromUserName>';
		$TextTpl.='<CreateTime>%s</CreateTime>';
		$TextTpl.='<MsgType><![CDATA[voice]]></MsgType>';
		$TextTpl.='<Voice><MediaId><![CDATA[%s]]></MediaId></Voice>';
		$TextTpl.='</xml>';
		$ResultStr=sprintf($TextTpl,$this->PostObj->FromUserName,$this->PostObj->ToUserName,NOW_TIME,$MediaId);
		die($ResultStr);
	}
	public function SendVideo($Title,$Description,$MediaId){
		$TextTpl='<xml>';
		$TextTpl.='<ToUserName><![CDATA[%s]]></ToUserName>';
		$TextTpl.='<FromUserName><![CDATA[%s]]></FromUserName>';
		$TextTpl.='<CreateTime>%s</CreateTime>';
		$TextTpl.='<MsgType><![CDATA[video]]></MsgType>';
		$TextTpl.='<Video>';
		$TextTpl.='<MediaId><![CDATA[%s]]></MediaId>';
		$TextTpl.='<Title><![CDATA[%s]]></Title>';
		$TextTpl.='<Description><![CDATA[%s]]></Description>';
		$TextTpl.='</Video>';
		$TextTpl.='</xml>';
		$ResultStr=sprintf($TextTpl,$this->PostObj->FromUserName,$this->PostObj->ToUserName,NOW_TIME,$MediaId,$Title,$Description);
		die($ResultStr);
	}
	public function SendMusic($Title,$Description,$MusicUrl,$HQMusicUrl,$ThumbMediaId){
		$TextTpl='<xml>';
		$TextTpl.='<ToUserName><![CDATA[%s]]></ToUserName>';
		$TextTpl.='<FromUserName><![CDATA[%s]]></FromUserName>';
		$TextTpl.='<CreateTime>%s</CreateTime>';
		$TextTpl.='<MsgType><![CDATA[music]]></MsgType>';
		$TextTpl.='<Music>';
		$TextTpl.='<Title><![CDATA[%s]]></Title>';
		$TextTpl.='<Description><![CDATA[%s]]></Description>';
		$TextTpl.='<MusicUrl><![CDATA[%s]]></MusicUrl>';
		//$TextTpl.='<HQMusicUrl><![CDATA[HQ_MUSIC_Url]]></HQMusicUrl>';
		$TextTpl.='<HQMusicUrl><![CDATA[%s]]></HQMusicUrl>';
		$TextTpl.='<ThumbMediaId><![CDATA[%s]]></ThumbMediaId>';
		$TextTpl.='</Music>';
		$TextTpl.='</xml>';
		$ResultStr=sprintf($TextTpl,$this->PostObj->FromUserName,$this->PostObj->ToUserName,NOW_TIME,$Title,$Description,$MusicUrl,$HQMusicUrl,$ThumbMediaId);
		file_put_contents('./z,txt',var_export($ResultStr,true));

		die($ResultStr);
	}
	public function SendNews($NewsArray){
		$TextTpl='<xml>';
		$TextTpl.='<ToUserName><![CDATA[%s]]></ToUserName>';
		$TextTpl.='<FromUserName><![CDATA[%s]]></FromUserName>';
		$TextTpl.='<CreateTime>%s</CreateTime>';
		$TextTpl.='<MsgType><![CDATA[news]]></MsgType>';
		$TextTpl.='<ArticleCount>%s</ArticleCount>';
		$TextTpl.='<Articles>%s</Articles>';
		$TextTpl.='</xml>';
		$Item='';
		foreach($NewsArray as $value){
			$Item.='<item>';
			$Item.='<Title><![CDATA['.$value['title'].']]></Title>';
			$Item.='<Description><![CDATA['.$value['description'].']]></Description>';
			$Item.='<PicUrl><![CDATA['.$value['picurl'].']]></PicUrl>';
			$Item.='<Url><![CDATA['.$value['url'].']]></Url>';
			$Item.='</item>';
		}
		$ResultStr=sprintf($TextTpl,$this->PostObj->FromUserName,$this->PostObj->ToUserName,NOW_TIME,count($NewsArray),$Item);

		die($ResultStr);
	}
	public function SendCustomerService(){
		$TextTpl='<xml>';
		$TextTpl.='<ToUserName><![CDATA[%s]]></ToUserName>';
		$TextTpl.='<FromUserName><![CDATA[%s]]></FromUserName>';
		$TextTpl.='<CreateTime>%s</CreateTime>';
		$TextTpl.='<MsgType><![CDATA[transfer_customer_service]]></MsgType>';
		$TextTpl.='</xml>';
		$ResultStr=sprintf($TextTpl,$this->PostObj->FromUserName,$this->PostObj->ToUserName,NOW_TIME);
		die($ResultStr);
	}
	public function SendCustomerOneService($Str)
	{
		$TextTpl='<xml>';
		$TextTpl.='<ToUserName><![CDATA[%s]]></ToUserName>';
		$TextTpl.='<FromUserName><![CDATA[%s]]></FromUserName>';
		$TextTpl.='<CreateTime>%s</CreateTime>';
		$TextTpl.='<MsgType><![CDATA[transfer_customer_service]]></MsgType>';
		$TextTpl.='<TransInfo>';
		$TextTpl.='<KfAccount>![CDATA[%s]]</KfAccount>';
		$TextTpl.='</TransInfo>';
		$TextTpl.='</xml>';
		$ResultStr=sprintf($TextTpl,$this->PostObj->FromUserName,$this->PostObj->ToUserName,NOW_TIME,$Str);
		die($ResultStr);
	}
}