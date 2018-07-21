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
	 * 签到
	 */
	private function sign($Parame){
		
		//return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>array('status'=>1,'day'=>1));
		
		
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>array('status'=>0,'day'=>1));
	}
	
	/**
	 * 兑领红包
	 */
	private function redPacket($Parame){
		
		$data 			= array();
		//用户id
		$uid 			= intval($Parame['uid']);
		$user = M('user')->field('current_xinlidou,current_xianglidou,current_fulidou')->where(array('id'=>$Parame['uid']))->find();
		$info = array();
		
		$info['xinlidou']['current_xinlidou'] 			= $user['current_xinlidou'];
		$info['xinlidou']['red_packet'] 				= 0;
		$info['xinlidou']['url'] 						= '';
		
		$t = mktime(23,59,59,date('m',NOW_TIME),date('d',NOW_TIME),date('Y',NOW_TIME));
		
		$count = M('red_packet')->where(array('uid'=>$uid,'type'=>1,'is_receive'=>0,'end_time'=>array('egt',$t)))->count();
		$info['xianglidou']['current_xianglidou'] 		= $user['current_xianglidou'];
		$info['xianglidou']['red_packet'] 				= $count;
		$info['xianglidou']['url'] 						= 'http://'.WEB_DOMAIN.'/Home/Index/redPacket/type/1/uid/'.$uid.'.html';
		
		$count = M('red_packet')->where(array('uid'=>$uid,'type'=>2,'is_receive'=>0,'end_time'=>array('egt',$t)))->count();
		$info['fulidou']['current_fulidou'] 			= $user['current_fulidou'];
		$info['fulidou']['red_packet'] 					= $count;
		$info['fulidou']['url'] 						= 'http://'.WEB_DOMAIN.'/Home/Index/redPacket/type/2/uid/'.$uid.'.html';
		
		$data['info']									= $info;
		
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 业务项目
	 */
	private function ywxm($Parame){
		$ywxm = M('article')->where(array('type'=>array('in','5,6,7')))->order('type asc')->limit(3)->select();
		$row = array();
		if(!empty($ywxm)){
			foreach ($ywxm as $key=>$value){
				$row[$key]['id'] 				= (string)$value['id'];
				$row[$key]['pic'] 				= (string)$value['pic'];
				$row[$key]['name'] 				= (string)$value['title'];
				$row[$key]['url'] 				= 'http://'.WEB_DOMAIN.'/Home/Index/article/type/'.$value['type'].'.html';
			}
		}
		$data['list']							= $row;
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 页面底部广告
	 */
	private function bottomAd($Parame){
		$banner = M('banner')->where(array('type'=>4, 'status'=>1))->order('create_time DESC')->find();
		$data 				= array();
		$data['picture'] 	= (string)$banner['picture'];
		$data['link'] 		= (string)$banner['link'];
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
			$url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=".C('GZH.APPID')."&secret=".C('GZH.APPSECRET')."&code=".$code."&grant_type=authorization_code";
			$result = CurlHttp($url);
			$result =json_decode($result,true);
			$url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$result['access_token']."&openid=".$result['openid']."&lang=zh_CN";
			$result = CurlHttp($url);
			$result =json_decode($result,true);
			
			M('user');
			
			return array('Code' =>'0','Msg'=>$this->Lang['100010'],'Data'=>$result);
		}
	}
	
	/**
	 * 页面底部广告
	 */
	private function getCoordinate($Parame){
		$uid 						= $Parame['uid'];
		$longitude 					= $Parame['longitude'];
		$latitude 					= $Parame['latitude'];
		$user = M('user')->field('longitude,latitude')->where(array('id'=>$uid))->find();
		if(empty($user['longitude']) || empty($user['latitude'])){
			M('user')->where(array('id'=>$uid))->save(array('longitude'=>$longitude, 'latitude'=>$latitude));
		}
		return array('Code' =>'0','Msg'=>$this->Lang['100010']);
	}
}
?>