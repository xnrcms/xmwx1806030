<?php
namespace Api\Helper;
/**
 * 广告
 * @author
 */
class IndexHelper extends BaseHelper{
	//初始化接口
	public function apiRun($parame = ''){
		//接口分发
		$Parame		= !empty($parame) ? json_decode($parame,true) : '';
		$ac			= $Parame['ac'];
		$isapi		= $Parame['isapi'];
		if ($isapi === true){
			return !empty($ac) ? $this->$ac($Parame) : array('Code' =>'100009','Msg'=>$this->Lang['100009']);
		}
		return array('Code' =>'100007','Msg'=>$this->Lang['100007']);
	}
	
	/**
	 * 首页
	 */
	private function index($Parame){
		$data 					= array();
		
		//轮播图
		$carousel 				= $this->getCarousel($Parame);
		$row 					= array();
		if(!empty($carousel)){
			foreach ($carousel as $key=>$value){
				$row[$key]['picture'] 	= $value['picture'];
				$row[$key]['link'] 		= $value['link'];
			}
		}
		$data['carousel']		= $row;
		
		//推荐商品
		$goods 					= $this->getGoods($Parame);
		$row 					= array();
		if(!empty($goods)){
			foreach ($goods as $key=>$value){
				$row[$key]['id'] 			= $value['id'];
				$row[$key]['goodsname'] 	= $value['goodsname'];
				$row[$key]['goodsimg'] 		= $value['goodsimg'];
				$row[$key]['goodsprice'] 	= $value['goodsprice'];
			}
		}
		$data['goods']		= $row;
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	/**
	 * 首页轮播图
	 */
	private function getCarousel($Parame){
		$carousel = M('banner')->where(array('type'=>1, 'status'=>1))->limit('0,3')->select();
		return $carousel = empty($carousel) ? '' : $carousel;
	}
	/**
	 * 首页推荐4个商家
	 */
	private function getShop(){
		$shop = M('shop')->where(array('is_recommend'=>1, 'check_status'=>2))->order('create_time ASC')->limit('0,4')->select();
		return $shop = empty($shop) ? '' : $shop;
	}
	/**
	 * 首页推荐3个商品
	 */
	private function getGoods(){
		$goods = M('goods')->where(array('is_recommend'=>1, 'status'=>1))->order('create_time ASC')->limit('0,4')->select();
		return $goods = empty($goods) ? '' : $goods;
	}
	
	
	/**
	 * 首页banner
	 */
	private function getBanner($Parame){
		$banner = M('banner')->where(array('type'=>2, 'status'=>1))->order('create_time DESC')->find();
		return $banner = empty($banner) ? '' : $banner;
	}
	
	/**
	 * 消息列表
	 */
	private function message($Parame){
		//消息
		$map['uid'] 	= array(array('eq',$Parame['uid']),array('eq',0), 'or');
		$map['type']  	= array('eq',1);
		
		//总数
		$count      	= M('message')->where($map)->count();
		$Page       	= new \Think\Page($count,10);
		
		$message 		= M('message')->where($map)->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$data = $row = array();
		if(!empty($message)){
			foreach ($message as $key=>$value){
				$row[$key]['id'] 			= $value['id'];
				$row[$key]['title'] 		= $value['title'];
				$row[$key]['description'] 	= $value['description'];
				$row[$key]['create_time'] 	= date('m/d',$value['create_time']);
			}
		}
		$data['list']			= $row;
		$data['page']			= $Parame['page'];
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 删除消息
	 */
	private function messageDel($Parame){
		$res = M('message')->where(array('id'=>$Parame['id']))->delete();
		if($res != false){
			return array('Code' =>'0','Msg'=>$this->Lang['100022']);
		}
		return array('Code' =>'100023','Msg'=>$this->Lang['100023']);
	}
	
	/**
	 * 获取地理位置
	 */
	public function getLocation($Parame){
		$data = array();
		//appId
		$data['appId'] 			= C('GZH.APPID');
		//timestamp
		$timestamp 				= NOW_TIME;
		$data['timestamp'] 		= $timestamp;
		//nonceStr
		$noncestr 				= randomString(16,7);
		$data['noncestr'] 		= $noncestr;
		//获取access_token
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".C('GZH.APPID')."&secret=".C('GZH.APPSECRET');
		
		
		
		$ch = curl_init();
		//设置选项，包括URL
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		
		//执行并获取HTML文档内容
		$a = curl_exec($ch);
		//释放curl句柄
		curl_close($ch);
		
		
		//$a = file_get_contents($url);
		
		p($a);die;
		
		
		$access_token_info = CurlHttp($url);
		$access_token_arr = json_decode($access_token_info, true);
		$access_token = $access_token_arr['access_token'];
		
		
		p($access_token);die;
		
		
		//获取ticket
		$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$access_token."&type=jsapi";
		$ticket_info = CurlHttp($url);
		$ticket_arr = json_decode($ticket_info, true);
		$ticket = $ticket_arr['ticket'];
		$params = array();
		$params['jsapi_ticket'] 	= $ticket;
		$params['noncestr'] 		= $noncestr;
		$params['timestamp'] 		= $timestamp;
		$params['url'] 				= $Parame['url'];
		ksort($params);
		$stringToBeSigned = "";
		$i = 0;
		foreach ($params as $k => $v) {
			if ($i == 0) {
				$stringToBeSigned .= "$k" . "=" . "$v";
			} else {
				$stringToBeSigned .= "&" . "$k" . "=" . "$v";
			}
			$i++;
		}
		$signature = sha1($stringToBeSigned);
		//signature
		$data['signature'] 		= $signature;
		$data['ticket'] 		= $ticket;
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 授权
	 */
	private function auth($Parame){
		$backUrl 	= $Parame['backUrl'];
		$code 		= $Parame['code'];
		if(empty($code)){
			$baseurl=urlencode($backUrl);
			$authUrl='https://open.weixin.qq.com/connect/oauth2/authorize?appid='.C('GZH.APPID').'&redirect_uri='.$baseurl.'&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect';
			return array('Code' =>'1','Msg'=>$this->Lang['100018'],'Data'=>array('authUrl'=>$authUrl));
		}elseif($code){
			$url				= "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".C('GZH.APPID')."&secret=".C('GZH.APPSECRET')."&code=".$code."&grant_type=authorization_code";
			$result 			= file_get_contents($url);
			$result 			= json_decode($result,true);
			$url 				= "https://api.weixin.qq.com/sns/userinfo?access_token=".$result['access_token']."&openid=".$result['openid']."&lang=zh_CN";
			$result 			= file_get_contents($url);
			$result 			= json_decode($result,true);
			$data 				= array();
			$data['openid'] 	= $result['openid'];
			$data['avatar'] 	= $result['headimgurl'];
			$data['nickname'] 	= $result['nickname'];
			$data['sex'] 		= $result['sex'];
			if($data['openid']){
				$user 			= M('user')->where(array('openid'=>$data['openid']))->find();
				if(!$user['id']){
					$user['id'] = M('user')->add($data);
				}
				$data['uid'] 	= $user['id'];
				$data['hashid']	= $this->create_hashid($user['id']);
				return array('Code' =>'0','Msg'=>'成功','Data'=>$data);
			}else{
				return array('Code' =>'1','Msg'=>'未知错误');
			}
		}
	}
}
?>