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
class OrderController extends BaseController {
	
	//订单
	public function index(){
		$shop_id 	= I('get.shop_id','','urldecode');
		if(empty($shop_id)){
			$this->error('店铺编号不存在');
		}
		//店铺信息
		$shop 		= M('shop')->where(array('code_url'=>$shop_id))->find();
		if(empty($shop)){
			$this->error('店铺不存在');
		}
		$this->assign('shop',$shop);
		//用户信息
		$user 		= M('user')->where(array('id'=>UID))->find();
		$this->assign('user',$user);
		$this->display();
	}
	
	/**
	 * 线下订单提交
	 */
	public function orderSubmit(){
		//用户id
		$uid 			= UID;
		if(!$uid){
			$this->ajaxReturn(array('Code' =>'1','Msg'=>'用户编号不存在'));
		}
		//商家id
		$shop_id 		= I('post.shop_id', 0, 'intval');
		if(!$shop_id){
			$this->ajaxReturn(array('Code' =>'1','Msg'=>'店铺编号不存在'));
		}
		//金额
		$money			= I('post.money', 0, 'trim');
		if($shop_id <= 0){
			$this->ajaxReturn(array('Code' =>'1','Msg'=>'请输入金额'));
		}
		//用户信息
		$user 			= M('user')->field('current_xinlidou,current_xianglidou')->where(array('id'=>$uid))->find();
		//鑫利豆
		$xinlidou 		= I('post.xinlidou', 0, 'trim');
		
		if($xinlidou != ''){
			if(!judge_decimal($xinlidou, 0)){
				$this->ajaxReturn(array('Code' =>'1','Msg'=>'鑫利豆格式错误'));
			}
			if($xinlidou > $user['current_xinlidou']){
				$this->ajaxReturn(array('Code' =>'1','Msg'=>'鑫利豆不能大于你现有的鑫利豆'));
			}
		}else{
			$xinlidou = 0;
		}
		//享利豆
		$xianglidou 	= I('post.xianglidou', 0, 'trim');
		if($xianglidou != ''){
			if(!judge_decimal($xianglidou, 0)){
				$this->ajaxReturn(array('Code' =>'1','Msg'=>'享利豆格式错误'));
			}
			if($xianglidou > $user['current_xianglidou']){
				$this->ajaxReturn(array('Code' =>'1','Msg'=>'享利豆不能大于你现有的享利豆'));
			}
		}else{
			$xianglidou = 0;
		}
		
		if(($xinlidou+$xianglidou) > $money){
			$this->ajaxReturn(array('Code' =>'1','Msg'=>'你使用的豆子不能超过订单的总金额'));
		}
		//支付金额
		$pay_money 				= bcsub($money,bcadd($xinlidou,$xianglidou,2),2);
		if($pay_money>0 && $pay_money < 0.1){
			//$this->ajaxReturn(array('Code' =>'1','Msg'=>'支付金额必须大于0.1元'));
		}
		
		$data  					= array();
		$data['uid']			= $uid;
		$data['shop_id']		= $shop_id;
		$data['order_no']		= create_orderid();
		$data['xinlidou']		= $xinlidou;
		$data['xianglidou']		= $xianglidou;
		$data['total_money']	= $money;
		$data['pay_money']		= $pay_money;
		$data['create_time']	= NOW_TIME;
		
		$res = M('business_order')->add($data);
		if($res){
			if($pay_money > 0){
				//商家增加鑫豆
				$this->ajaxReturn(array('Code' =>'0','Msg'=>'请求成功','Data'=>array('oid'=>$data['order_no'], 'money'=>$pay_money,  'xinlidou'=>$data['xinlidou'], 'xianglidou'=>$data['xianglidou'])));
			}else{	//全额抵扣,不用支付金额
				//更改订单状态
				M('business_order')->where(array('id'=>$res))->save(array('pay_status'=>1, 'pay_time'=>NOW_TIME));
				//用户减去鑫豆
				$userRow 							= array();
				$userRow['current_xinlidou'] 		= array('exp',"current_xinlidou-$xinlidou");
				$userRow['current_xianglidou'] 		= array('exp',"current_xianglidou-$xianglidou");
				M('user')->where(array('id'=>$uid))->save($userRow);
				//商家增加鑫豆
				$b_uid = M('shop')->where(array('id'=>$shop_id))->getField('uid');
				$businessUserRow 						= array();
				$businessUserRow['total_xinlidou'] 		= array('exp',"total_xinlidou+$xinlidou");
				$businessUserRow['current_xinlidou'] 	= array('exp',"current_xinlidou+$xinlidou");
				$businessUserRow['total_xianglidou'] 	= array('exp',"total_xianglidou+$xianglidou");
				$businessUserRow['current_xianglidou'] 	= array('exp',"current_xianglidou+$xianglidou");
				M('user')->where(array('id'=>$b_uid))->save($businessUserRow);
				$this->ajaxReturn(array('Code' =>'0','Msg'=>'请求成功','Data'=>array('oid'=>$data['order_no'], 'money'=>0,  'xinlidou'=>$data['xinlidou'], 'xianglidou'=>$data['xianglidou'])));
			}
		}else{
			$this->ajaxReturn(array('Code' =>'1','Msg'=>'请求失败'));
		}
	}
	
}