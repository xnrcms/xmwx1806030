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
class PayController extends Controller {
	
	
	public function notify(){
		$data = file_get_contents('php://input');
		$data = json_decode($data,true);
		
		file_put_contents('a/5.txt',var_export($data, TRUE));
		
		if($data['partnerOrderStatus'] == 'SUCCESS'){
			$order = M('business_order')->where(array('order_no'=>$data['outOrderId']))->find();
			if($data['amount'] != $order['pay_money']){
				echo 'FAIL';
				die;
			}
			if($order){
				/**********************上线注释****************************/
				$order['pay_money'] = $order['pay_money']*1000;
				/**********************上线注释****************************/
				//更改订单状态
				M('business_order')->where(array('id'=>$order['id']))->save(array('pay_status'=>1, 'pay_time'=>NOW_TIME));
				
				//用户减去鑫豆
				$userRow 						= array();
				$userRow['current_xinlidou'] 	= array('exp',"current_xinlidou-{$order['xinlidou']}");
				$userRow['current_xianglidou'] 	= array('exp',"current_xianglidou-{$order['xianglidou']}");
				M('user')->where(array('id'=>$order['uid']))->save($userRow);
				
				//商家增加鑫豆和钱
				$b_uid = M('shop')->where(array('id'=>$order['shop_id']))->getField('uid');
				$businessUserRow 						= array();
				$businessUserRow['total_money'] 		= array('exp',"total_money+{$order['pay_money']}");
				$businessUserRow['current_money'] 		= array('exp',"current_money+{$order['pay_money']}");
				$businessUserRow['total_xinlidou'] 		= array('exp',"total_xinlidou+{$order['xinlidou']}");
				$businessUserRow['current_xinlidou'] 	= array('exp',"current_xinlidou+{$order['xinlidou']}");
				$businessUserRow['total_xianglidou'] 	= array('exp',"total_xianglidou+{$order['xianglidou']}");
				$businessUserRow['current_xianglidou'] 	= array('exp',"current_xianglidou+{$order['xianglidou']}");
				M('user')->where(array('id'=>$b_uid))->save($businessUserRow);
				
				//生成红包
				$this->setRedPacket($order['uid'], $order['shop_id'], $order['pay_money']);
					
				//生成分销
				$this->setDistribution($order['uid'], $order['shop_id'], $order['pay_money']);
					
				//生成营业交易奖励
				$this->setShopDistribution($order['uid'], $order['shop_id'], $order['pay_money']);
				
				echo 'SUCCESS';
				die;
			}
		}
	}
	
	/**
	 * 生成红包
	 */
	private function setRedPacket($uid, $shop_id, $money){
		$red_packet_proportion = M('red_packet_proportion')->field('type,number,proportion')->select();
		foreach ($red_packet_proportion as $key=>$value){
			$proportionArr[$value['number']] = array('type'=>$value['type'],'proportion'=>$value['proportion']);
		}
		ksort($proportionArr);
		$row 						= array();
		
		//商家协定让利促销的折扣
		$grade = M('shop')->where(array('id'=>$shop_id))->getField('grade');
		$proportion = M('shop_proportion')->where(array('grade'=>$grade))->getField('proportion');
		
		foreach ($proportionArr as $key=>$value){
			$row['uid'] 			= $uid;
			$row['shop_id'] 		= $shop_id;
			$row['type'] 			= $value['type'];
			$row['money'] 			= $money;
			$row['proportion'] 		= $value['proportion'];
			$row['num'] 			= $money*$proportion*$value['proportion'];
			if($key == 1){
				if(date('G', NOW_TIME) < 23){
					$_start_time	= mktime(0,0,0,date('m',NOW_TIME),date('d',NOW_TIME),date('Y',NOW_TIME));
				}else{
					$_start_time	= mktime(0,0,0,date('m',NOW_TIME),date('d',NOW_TIME)+1,date('Y',NOW_TIME));
				}
				$start_time			= NOW_TIME;
			}else{
				$interval 			= 24*60*60*($key-1);
				$start_time			= $_start_time+$interval;
			}
			$row['start_time'] 		= $start_time;
			if($key == 1){
				if(date('G', NOW_TIME) < 23){
					$end_time		= mktime(23,59,59,date('m',NOW_TIME),date('d',NOW_TIME),date('Y',NOW_TIME));
				}else{
					$end_time		= mktime(23,59,59,date('m',NOW_TIME),date('d',NOW_TIME)+1,date('Y',NOW_TIME));
				}
				$_end_time			= $end_time;
			}else{
				$interval 			= 24*60*60*($key-1);
				$end_time			= $_end_time+$interval;
			}
			$row['end_time'] 		= $end_time;
				
			$row['packet_date'] 	= strtotime(date('Y-m-d',$end_time));
				
			$packetInfo 			= M('red_packet')->where(array('uid'=>$uid, 'packet_date'=>$row['packet_date'], 'create_time'=>array('lt',NOW_TIME)))->order('create_time desc')->find();
			if(empty($packetInfo['packet_num'])){
				$row['packet_num'] 	= 1;
			}else{
				$row['packet_num'] 	= $packetInfo['packet_num']+1;
			}
				
			$row['create_time'] 	= NOW_TIME;
			//插入红包表
			M('red_packet')->add($row);
		}
	}
	
	/**
	 * 生成分销
	 */
	private function setDistribution($uid, $shop_id, $money){
		//商家协定让利促销的折扣
		$grade = M('shop')->where(array('id'=>$shop_id))->getField('grade');
		$proportion = M('shop_proportion')->where(array('grade'=>$grade))->getField('proportion');
		//推荐会员获取的鑫利豆
		$xinlidou = $money*$proportion*0.05;
		//分销产生两级 产生鑫利豆
		$fx_uid = array();
		$first_id = M('user')->where(array('id'=>$uid))->getField('pid');
		if($first_id > 0){
			$fx_uid[] = $first_id;
			//二级分销
			$second_id = M('user')->where(array('id'=>$first_id))->getField('pid');
			if($second_id > 0){
				$fx_uid[] = $second_id;
			}
		}
		if(!empty($fx_uid)){
			$row 						= array();
			$row['total_xinlidou'] 		= array('exp',"total_xinlidou+$xinlidou");
			$row['current_xinlidou'] 	= array('exp',"current_xinlidou+$xinlidou");
			M('user')->where(array('id'=>array('in',$fx_uid)))->save($row);
		}
	}
	
	/**
	 * 生成营业交易奖励
	 */
	private function setShopDistribution($uid, $shop_id, $money){
		//商家协定让利促销的折扣
		$shop 			= M('shop')->field('uid,grade')->where(array('id'=>$shop_id))->find();
		$proportion 	= M('shop_proportion')->where(array('grade'=>$shop['grade']))->getField('proportion');
		//推荐会员获取的鑫利豆
		$xinlidou 		= $money*$proportion*0.05;
		//分销产生两级 产生鑫利豆
		$p_uid 			= M('user')->where(array('id'=>$shop['uid']))->getField('pid');
		if($p_uid > 0){
			$row 						= array();
			$row['total_xinlidou'] 		= array('exp',"total_xinlidou+$xinlidou");
			$row['current_xinlidou'] 	= array('exp',"current_xinlidou+$xinlidou");
			M('user')->where(array('id'=>$p_uid))->save($row);
		}
	}
}