<?php
namespace Api\Helper;

/**
 * 订单详情
 * @author 
 */
class BusinessHelper extends BaseHelper{
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
	
	//登录
	private function login($Parame){
		//登录验证
		$username 	= safe_replace($Parame['username']);//过滤
		$password	= $Parame['password'];
	
		$user = M('shop')->where(array('phone'=>$username, 'status'=>1))->find();
		if(empty($user['id'])){
			return array('Code' =>'100033','Msg'=>$this->Lang['100033']);
		}
		if($user['password'] != $password){
			return array('Code' =>'100034','Msg'=>$this->Lang['100034']);
		}
		//UC登录成功
		if($user['id'] > 0){
			if(!empty($user) && $user['id'] > 0){
				/*登录成功后*/
				$data					= array();
				$data['shop_id']		= intval($user['id']);
				$data['face']			= $user['face'];
				$data['name']			= $user['shop_name'];
				return array('Code' =>'0','Msg'=>$this->Lang['100049'],'Data'=>$data);
			} else {
				return array('Code' =>'100037','Msg'=>$this->Lang['100037']);
			}
		} else { //登录失败
			return array('Code' => '100037','Msg'=>$this->Lang['100037']);
		}
	}
	
	private function orderDetail($Parame){
		$order = M('order')->where(array('id'=>$Parame['id']))->find();
		$data = array();
		if(!empty($order)){
			//收货人
			$data['rname'] 				= $order['rname'];
			//电话
			$data['phone'] 				= $order['phone'];
			//提货地址
			$data['address'] 			= $order['address'];
			//订单商品信息
			$goodsInfo 					= M('order_desc')->field('gname,gImg,num,price')->where(array('oid'=>$order['id']))->select();
			$data['goods_info']			= $goodsInfo;
			//订单总金额
			$data['total_money'] 		= $order['total_money'];
			//订单号
			$data['order_no'] 			= $order['order_no'];
			//订单状态
			$data['status'] 			= $order['status'];
			//是否发送通知
			$data['is_send'] 			= $order['is_send'];
			//下单时间
			$data['create_time'] 		= date('Y/m/d H:i:s', $order['create_time']);
			//付款时间
			$data['pay_time'] 			= date('Y/m/d H:i:s', $order['pay_time']);
		}
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	//订单列表
	private function orderList($Parame){
		
		$page						= $Parame['page'];
		$limit						= 10;
		//获取数据
		$MainTab					= 'order';
		$MainAlias					= 'main';
		$MainField					= array('id,order_no,status,total_money,create_time,is_send');
		
		//主表模型
		$MainModel 					= M($MainTab)->alias($MainAlias);
		
		$RelationTab				= array('order_desc'=>array('Ralias'=>'o_d','Ron'=>'o_d ON o_d.oid=main.id','Rfield'=>array('gname,gImg,num,price')));
		$RelationTab				= $this->getRelationTab($RelationTab);
		$tables	  					= $RelationTab['tables'];
		$RelationFields				= $RelationTab['fields'];
		$model						= !empty($tables) ? $MainModel->join ( $tables ,'LEFT'): $MainModel;
		
		//检索条件
		$map 						= array();
		$map['main.shop_id']		= $Parame['shop_id'];
		$map['main.is_delete']		= 1;
		$map['main.pay_status']		= 1;
		if(!empty($Parame['keyword'])){
			$map['phone'] 			= array('like', '%'.$Parame['keyword'].'%');
		}
		$type 						= $Parame['type'];
		if($type == 1){
			$map['main.create_time']		= array('EGT',mktime(0,0,0,date('m'),date('d'),date('Y')));
		}elseif($type == 2){
			$map['main.create_time']		= array('LT',mktime(0,0,0,date('m'),date('d'),date('Y')));
		}
		//排序
		$order						= $MainAlias.'.id DESC';
		//检索字段
		$fields						= (empty($MainField) ? get_fields_string($MainModel->getDbFields(),$MainAlias).',' : get_fields_string($MainField,$MainAlias).',') . $RelationFields;
		$fields						= trim($fields,',');
		
		//列表数据
		$list 						= $this->getLists($model,$map,$order,$fields,$page,$limit,false);
		if (!empty($list)){
			foreach ($list as $k=>$v){
				$arr[$v['id']]['id']			= $v['id'];
				$arr[$v['id']]['order_no']		= $v['order_no'];
				$arr[$v['id']]['status']		= $v['status'];
				$arr[$v['id']]['is_send']		= $v['is_send'];
				$arr[$v['id']]['total_money']	= $v['total_money'];
				$arr[$v['id']]['create_time']	= date('Y-m-d', $v['create_time']);
				$arr[$v['id']]['sublist'][] 	= $v;
			}
		}
		$data['list']			= empty($list) ? array() : array_values($arr);
		$data['page']			= $Parame['page'];
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 确认订单
	 */
	private function sendMessage($Parame){
		$id 	= intval($Parame['id']);
		if($id <= 0){
			return array('Code' =>'101729','Msg'=>$this->Lang['101729']);
		}
		$order = M('order')->where(array('id'=>$id))->find();
		$uid = $order['uid'];
		$shopId = $order['shop_id'];
		if(!$uid){
			return array('Code' =>'1','Msg'=>'订单信息错误');
		}
		$shopName = M('shop')->where(array('id'=>$shopId))->getField('shop_name');
		$res = M('message')->add(array(
				'type' => 1,
				'uid' => $uid,
				'title' => '取货提醒',
				'description' => '您的订单已经到货,请到'.$shopName.'来取货',
				'create_time' => NOW_TIME
		));
		$res1 = M('order')->where(array('id'=>$id))->save(array('is_send'=>1));
		if($res != false && $res1 != false){
			return array('Code' =>'0','Msg'=>$this->Lang['100018']);
		}
		return array('Code' =>'100019','Msg'=>$this->Lang['100019']);
	}
}
?>