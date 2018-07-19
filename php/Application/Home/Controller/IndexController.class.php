<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use Think\Controller;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class IndexController extends Controller {
	
	//下载
	public function down(){
		$filepath = dirname(__FILE__).'../../../../Uploads/Download/app-release.apk';
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.basename($filepath));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($filepath));
		readfile($filepath);
	}
	
	//商品详情
	public function detail(){
		$goods = M('goods')->where(array('id'=>I('get.goods_id',0,'intval')))->find();
		$data = array();
		if(!empty($goods)){
			$data['content'] 			= $goods['content'];
		}
		$this->assign('data', $data);
		$this->display();
	}
	
	//详情
	public function article(){
		$goods = M('article')->where(array('type'=>I('get.type',0,'intval'), 'status'=>1))->find();
		$data = array();
		if(!empty($goods)){
			$data['title'] 				= $goods['title'];
			$data['content'] 			= $goods['content'];
		}
		$this->assign('data', $data);
		$this->display();
	}
	
	//领取红包
	public function redPacket(){
		//用户信息
		$uid = I('get.uid',0,'intval');
		if($uid < 1){
			$this->error('用户参数错误');
		}
		//红包类型
		$type = I('get.type',0,'intval');
		if($type < 1){
			$this->error('鑫豆参数错误');
		}
		$where 							= array();
		$where['uid'] 					= $uid;
		$where['type'] 					= $type;
		$where['is_receive'] 			= 0;
		$where['start_time'] 			= array('ELT',NOW_TIME);
		$where['end_time'] 				= array('EGT',NOW_TIME);
		$redPacket 						= M('red_packet')->where($where)->order('create_time asc')->find();
		if($redPacket['type'] == 1){		//享利豆
			$redPacket['type_name'] 	= '享利豆';
		}elseif($redPacket['type'] == 2){	//福利豆
			$redPacket['type_name'] 	= '福利豆';
		}
		$redPacket['shop_name']			= M('shop')->where(array('id'=>$redPacket['shop_id']))->getField('shop_name');
		if($redPacket['order_no'] > 1){
			$redPacket['pic'] 			= M('advertisement')->where(array('order_no'=>$redPacket['order_no']))->getField('content');
		}else{
			$redPacket['pic'] 			= 'http://'.WEB_DOMAIN.'/Public/Home/img/red_packet.jpg';
		}
		$this->assign('redPacket', $redPacket);
		$this->display();
	}
	
	//领取红包
	public function getRedPacket(){
		//红包id
		$id 			= I('get.id',0,'intval');
		if($id < 1){
			$this->ajaxReturn(array('Code' =>'1','Msg'=>"红包参数错误!"));
		}
		$redPacket = M('red_packet')->where(array('id'=>$id))->find();
		if($redPacket['is_receive'] == 1){
			$this->ajaxReturn(array('Code' =>'1','Msg'=>"红包已领取!"));
		}
		if(!empty($redPacket)){
			$row 							= array();
			if($redPacket['type'] == 1){	//享利豆
				$row['total_xianglidou'] 	= array('exp',"total_xianglidou+{$redPacket['num']}");
				$row['current_xianglidou'] 	= array('exp',"current_xianglidou+{$redPacket['num']}");
				$type_name 					= '享利豆';
			}elseif($redPacket['type'] == 2){	//福利豆
				$row['total_fulidou'] 		= array('exp',"total_xinlidou+{$redPacket['num']}");
				$row['current_fulidou'] 	= array('exp',"current_xinlidou+{$redPacket['num']}");
				$type_name 					= '福利豆';
			}
			$res = M('user')->where(array('id'=>$redPacket['uid']))->save($row);
			if($res){
				M('red_packet')->where(array('id'=>$id))->save(array('is_receive'=>1, 'receive_time'=>NOW_TIME));
				$this->ajaxReturn(array('Code' =>'0','Msg'=>"领取成功!",'Data'=>array('num'=>$redPacket['num'], 'type_name'=>$type_name)));
			}
			$this->ajaxReturn(array('Code' =>'1','Msg'=>"未知错误!"));
		}else{
			$this->ajaxReturn(array('Code' =>'1','Msg'=>"红包信息不存在!"));
		}
	}
	
	//分享页面
	public function share(){
		//用户信息
		$uid = I('get.pid',0,'intval');
		if($uid < 1){
			$this->error('用户参数错误');
		}
		$user = M('user')->field('id, code, avatar, nickname')->where(array('id'=>$uid, 'type'=>1))->find();
		if($user['id'] < 1){
			$this->error('用户信息不存在');
		}
		$this->assign('user', $user);
		$this->display();
	}
	
	//邀请页面
	public function invite(){
		//用户信息
		$uid = I('get.pid',0,'intval');
		if($uid < 1){
			$this->error('用户参数错误');
		}
		$where 			= array();
		$where['pid']	= $uid;
		$where['type']	= 1;
		
		$count = M('user')->where($where)->count();
		$this->assign('count', $count);
		
		$user = M('user')->field('id, phone, avatar')->where($where)->select();
		
		$this->assign('user', $user);
		
		//分享
		$platformConfig = M('platform_config')->select();
		$share = array();
		foreach ($platformConfig as $key=>$value){
			if(strtoupper($value['name']) == 'SHARE_CONTENT'){
				$share['content'] = $value['value'];
			}
			if(strtoupper($value['name']) == 'SHARE_URL'){
				$share['url'] = 'http://'.WEB_DOMAIN.'/Home/Index/share/pid/'.$uid.'.html';
			}
			if(strtoupper($value['name']) == 'SHARE_IMG'){
				$share['img'] = $value['value'];
			}
			if(strtoupper($value['name']) == 'SHARE_TITLE'){
				$share['title'] = $value['value'];
			}
		}
		$this->assign('share', $share);
		
		$this->display();
	}
	
	//扫一扫
	public function scan(){
		//获取access_token
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".C('GZH.APPID')."&secret=".C('GZH.KEY');
		$access_token_info = CurlHttp($url);
		$access_token_arr = json_decode($access_token_info, true);
		$access_token = $access_token_arr['access_token'];
		//获取ticket
		$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$access_token."&type=jsapi";
		$ticket_info = CurlHttp($url);
		$ticket_arr = json_decode($ticket_info, true);
		$ticket = $ticket_arr['ticket'];
		$noncestr = randomString(16,7);
		$this->assign('noncestr',$noncestr);
		$timestamp = NOW_TIME;
		$this->assign('timestamp',$timestamp);
		$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$params = array();
		$params['jsapi_ticket'] = $ticket;
		$params['noncestr'] = $noncestr;
		$params['timestamp'] = $timestamp;
		$params['url'] = $url;
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
		$this->assign('signature',$signature);
		
		$this->display();
	}
	
}