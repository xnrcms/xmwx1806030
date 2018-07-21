<?php
namespace Api\Helper;

/**
 * 订单详情
 * @author 
 */
class OrderHelper extends BaseHelper{
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
	
	private function order($Parame){
		$uid 			= intval($Parame['uid']);
		//购买类型 1立即购买 2购物车
		$buyType 		= intval($Parame['type']);
		if($buyType == 1){
			$info = json_decode($Parame['info'],true);
			//商品id
			$gid = intval($info['gid']);
			if(empty($gid)){
				return array('Code' =>'101704','Msg'=>$this->Lang['101704']);
			}
			//商品数量
			$num = intval($info['num']);
			if($num < 1){
				return array('Code' =>'101706','Msg'=>$this->Lang['101706']);
			}
			$param = array($gid=>$num);
		}elseif($buyType == 2){
			//购物车id([1,2,3])
			$cartId = trim($Parame['cartId']);
			if($cartId == ''){
				return array('Code' =>'101708','Msg'=>$this->Lang['101708']);
			}
			$cartIdArr = explode(',', $cartId);
			if(empty($cartIdArr)){
				return array('Code' =>'101708','Msg'=>$this->Lang['101708']);
			}
			$cart = M('cart')->field('id,gid,gnum')->where(array('id'=>array('in',$cartIdArr), 'uid'=>$uid))->select();
			if(empty($cart)){
				return array('Code' =>'101710','Msg'=>$this->Lang['101710']);
			}
			$attridArr = $cartByAttrid = array();
			foreach ($cart as $key=>$value){
				$param[$value['gid']] = $value['gnum'];
			}
		}
		
		//商品数据
		$goods = M('goods')->field('id, goodsname, goodsimg, goodsprice, stock')->where(array('id'=>array('in',array_keys($param))))->select();
		
		if(empty($goods)){
			return array('Code' =>'101709','Msg'=>$this->Lang['101709']);
		}
		$info = $goodsInfo = $totalPrice = $totalPoints = $totalNum = '';
		foreach($goods as $key=>$value){
			$goodsInfo[$key]['goodsId'] 	= $value['id'];
			$goodsInfo[$key]['goodsImg'] 	= $value['goodsimg'];
			$goodsInfo[$key]['goodsName'] 	= $value['goodsname'];
			$goodsInfo[$key]['orderNum'] 	= $param[$value['id']];
			$goodsInfo[$key]['price'] 		= $value['goodsprice'];
			//总价格
			$totalPrice += $goodsInfo[$key]['price']*$goodsInfo[$key]['orderNum'];
			//总数量
			$totalNum += $goodsInfo[$key]['orderNum'];
		}
		//商品信息
		$info['goodsInfo'] 				= $goodsInfo;
		//总金额
		$info['totalPrice'] 			= $totalPrice;	
		//总数量
		$info['totalNum'] 				= $totalNum;	
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$info);
	}
	
	
	/**
	 * 提交订单
	 *
	 */
	public function orderSubmit($Parame){
		//用户id
		$uid 			= intval($Parame['uid']);
		$shopId			= intval($Parame['shopId']);
		//地址
		$shop 			= M('shop')->field('id,province,area,county,address')->where(array('uid'=>$uid, 'is_selected'=>1))->find();
		
		//商品信息
		$info = json_decode($Parame['info'],true);
		if(empty($info[0]['gid'])){
			return array('Code' =>'101707','Msg'=>$this->Lang['101707']);
		}
		foreach ($info as $key=>$value){
			$param[$value['gid']] 	= $value['num'];
		}
		//商品数据
		$goods = M('goods')->field('id, goodsname, goodsimg, goodsprice')->where(array('id'=>array('in',array_keys($param))))->select();
		if(empty($goods)){
			return array('Code' =>'101709','Msg'=>$this->Lang['101709']);
		}
		$info = $goodsInfo = $totalPrice = $totalNum = '';
		foreach($goods as $key=>$value){
			$goodsInfo[$key]['goodsId'] 	= $value['id'];
			$goodsInfo[$key]['goodsImg'] 	= $value['goodsimg'];
			$goodsInfo[$key]['goodsName'] 	= $value['goodsname'];
			$goodsInfo[$key]['orderNum'] 	= $param[$value['id']];
			$goodsInfo[$key]['price'] 		= $value['goodsprice'];
			//总价格
			$totalPrice 					+= $goodsInfo[$key]['price']*$goodsInfo[$key]['orderNum'];
			//总数量
			$totalNum 						+= $goodsInfo[$key]['orderNum'];
		}
		//判断库存
		foreach ($goodsInfo as $key=>$value) {
			$stock = M('goods')->where(array('id'=>$value['goodsId']))->getField('stock');
			if($value['orderNum']<1){
				return array('Code' =>'101706','Msg'=>$this->Lang['101706']);
			}
			if($value['orderNum']>$stock){
				return array('Code' =>'101713','Msg'=>$this->Lang['101713']);
			}
		}
		//入库数据
		$data 							= array();
		$data['uid'] 					= $uid;
		$data['order_no'] 				= create_orderid();
		$data['out_trade_no'] 			= 'REWORD'.date('YmdHis',NOW_TIME).randomString('6',0);
		$data['goods_total_money'] 		= $totalPrice;
		$data['gnum'] 					= $totalNum;
		$data['create_time']			= NOW_TIME;
		//地址
		$shop 							= M('shop')->where(array('id'=>$shopId))->find();
		$data['province']				= empty($shop['province'])?'':$shop['province'];
		$data['city']					= empty($shop['city'])?'':$shop['city'];
		$data['county']					= empty($shop['county'])?'':$shop['county'];
		$data['address']				= empty($shop['address'])?'':$shop['address'];
		$data['rname']					= $Parame['rname'];
		$data['phone']					= $Parame['phone'];
		
		//应付金额
		$data['total_money']			= $data['goods_total_money'];
		$data['pay_status']				= 0;
		$data['status']					= 1;
		//添加到订单表
		$res = M('order')->add($data);
		if($res){
			$dataDesc 					= array();
			foreach ($goodsInfo as $key=>$value){
				$dataDesc[$key]['uid']			= $uid;
				$dataDesc[$key]['oid']			= $res;
				$dataDesc[$key]['gid']			= $value['goodsId'];
				$dataDesc[$key]['gname']		= $value['goodsName'];
				$dataDesc[$key]['gImg']			= $value['goodsImg'];
				if($value['orderNum']<1){
					return array('Code' =>'101706','Msg'=>$this->Lang['101706']);
				}else{
					$dataDesc[$key]['num']		= $value['orderNum'];
				}
				$dataDesc[$key]['price']		= $value['price'];
				//减库存
				M('goods')->where(array('id'=>$value['goodsId']))->setDec('stock', $value['orderNum']);
				//加销量
				M('goods')->where(array('id'=>$value['goodsId']))->setInc('salenum', $value['orderNum']);
			}
			//添加到订单详情表
			$result = M('orderDesc')->addAll(array_values($dataDesc));
			 
			//判断商品类型用什么支付
			if($result){
				//删除购物车
				
				//购买类型 1立即购买 2购物车
				$buyType 		= intval($Parame['type']);
				//操作
				return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>array('oid'=>$res,'money'=>$data['total_money']));
			}
		}
		return array('Code' =>'101211','Msg'=>$this->Lang['101211']);
	}
	
	
	
	private function orderDetail($Parame){
		$order = M('order')->where(array('order_no'=>$Parame['trade_no']))->find();
		$data = array();
		if(!empty($order)){
			//商家信息
			$shopInfo = M('shop')->field('shop_name,create_time')->where(array('id'=>$order['shop_id']))->find();
			//商家名称
			$data['shop_name'] 			= $shopInfo['shop_name'];
			//商家创建时间
			$data['shop_time'] 			= date('Y/m/d H:i:s', $shopInfo['create_time']);
			//订单金额
			$data['total_money'] 		= $order['total_money'];
			//支付折扣
			$data['discount'] 			= $order['discount'];
			//折扣金额
			$data['discount_money'] 	= $order['total_money']-$order['money'];
			//实付金额
			$data['money'] 				= $order['money'];
			//订单号
			$data['trade_no'] 			= $order['trade_no'];
			//支付方式	1支付宝 2微信
			$data['pay_type'] 			= $order['pay_type'];
			//下单时间
			$data['create_time'] 		= date('Y/m/d H:i:s', $order['create_time']);
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
		$MainField					= array('id,order_no,status,is_notice,total_money');
		
		//主表模型
		$MainModel 					= M($MainTab)->alias($MainAlias);
		
		$RelationTab				= array('order_desc'=>array('Ralias'=>'o_d','Ron'=>'o_d ON o_d.oid=main.id','Rfield'=>array('gname,gImg,avalue,num,price')));
		$RelationTab				= $this->getRelationTab($RelationTab);
		$tables	  					= $RelationTab['tables'];
		$RelationFields				= $RelationTab['fields'];
		$model						= !empty($tables) ? $MainModel->join ( $tables ,'LEFT'): $MainModel;
		
		//检索条件
		$map 						= array();
		//$map['main.pay_status']		= 1;
		$map['main.uid']			= $Parame['uid'];
		$map['main.is_delete']		= 1;
		$type 						= $Parame['type'];
		if($type == 1){
			$map['main.status']		= 1;
		}elseif($type == 2){
			$map['main.status']		= 2;
		}elseif($type == 3){
			$map['main.status']		= 3;
		}elseif($type == 4){
			$map['main.status']		= array('in',array(4,5,6,7));
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
				$arr[$v['id']]['is_notice']		= $v['is_notice'];
				$arr[$v['id']]['total_money']	= $v['total_money'];
				$arr[$v['id']]['sublist'][] 	= $v;
				
			}
		}
		$data['list']			= empty($list) ? array() : array_values($arr);
		$data['page']			= $Parame['page'];
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 取消订单
	 */
	private function orderCancel($Parame){
		$id 	= intval($Parame['id']);
		$uid 	= intval($Parame['uid']);
		if($id <= 0){
			return array('Code' =>'101729','Msg'=>$this->Lang['101729']);
		}
		$res = M('order')->where(array('uid'=>$uid, 'id'=>$id))->save(array('is_delete'=>2));
		if($res != false){
			return array('Code' =>'0','Msg'=>$this->Lang['100018']);
		}
		return array('Code' =>'100019','Msg'=>$this->Lang['100019']);
	}
	
	/**
	 * 确认订单
	 */
	private function orderConfirm($Parame){
		$id 	= intval($Parame['id']);
		$uid 	= intval($Parame['uid']);
		if($id <= 0){
			return array('Code' =>'101729','Msg'=>$this->Lang['101729']);
		}
		$res = M('order')->where(array('uid'=>$uid, 'id'=>$id))->save(array('status'=>4, 'receipt_time'=>NOW_TIME));
		if($res != false){
			return array('Code' =>'0','Msg'=>$this->Lang['100018']);
		}
		return array('Code' =>'100019','Msg'=>$this->Lang['100019']);
	}
	
	/**
	 * 催促发货
	 */
	private function orderNotice($Parame){
		$id 	= intval($Parame['id']);
		$uid 	= intval($Parame['uid']);
		if($id <= 0){
			return array('Code' =>'101729','Msg'=>$this->Lang['101729']);
		}
		$res = M('order')->where(array('uid'=>$uid, 'id'=>$id))->save(array('is_notice'=>1));
		if($res != false){
			return array('Code' =>'0','Msg'=>$this->Lang['100018']);
		}
		return array('Code' =>'100019','Msg'=>$this->Lang['100019']);
	}
	
	/**
	 * 查看物流
	 */
	private function orderLogistics($Parame){
		$id 	= intval($Parame['id']);
		$uid 	= intval($Parame['uid']);
		if($id <= 0){
			return array('Code' =>'101729','Msg'=>$this->Lang['101729']);
		}
		$order = M('order')->where(array('uid'=>$uid, 'id'=>$id))->find();
		$data = array();
		if(!empty($order)){
			$data['order_no']		= $order['order_no'];
			$data['express_no']		= $order['express_no'];
			$data['pic']			= M('order_desc')->where(array('oid'=>$order['id']))->order('id asc')->limit(1)->getField('gImg');
			$data['tel']			= M('platform_config')->where(array('name'=>'MOBILE'))->getField('value');
			$data['address']		= $order['province'].$order['city'].$order['county'].$order['address'];
			
			$requestData= "{'ShipperCode':'".$order['express_company_code']."','LogisticCode':'".$order['express_no']."'}";
			$datas = array(
					'EBusinessID' => C('KUAIDINIAO.EBusinessID'),
					'RequestType' => '1002',
					'RequestData' => urlencode($requestData) ,
					'DataType' => '2',
			);
			$datas['DataSign'] = kdn_encrypt($requestData, C('KUAIDINIAO.AppKey'));
			$result = json_decode(sendPost(C('KUAIDINIAO.ReqURL'), $datas),true);
			$logistics	=array(
					'AcceptStation'	=> '商家正在处理您的订单',
					'AcceptTime1'	=> date('n/j', $order['send_time']),
					'AcceptTime2'	=> date('H:i', $order['send_time']),
					'AcceptStatus'	=> '已下单'	
			);
			if($result['Success'] == 1){
				foreach ($result['Traces'] as $key=>$value){
					$data['logistics'][$key]['AcceptStation'] 	= $value['AcceptStation'];
					$data['logistics'][$key]['AcceptTime1'] 	= date('n/j', strtotime($value['AcceptTime']));
					$data['logistics'][$key]['AcceptTime2'] 	= date('H:i', strtotime($value['AcceptTime']));
					if(strpos($value['AcceptStation'],"派送") !== false){
						$data['logistics'][$key]['AcceptStatus'] 	= '派送中';
					}elseif(strpos($value['AcceptStation'],"揽收") !== false){
						$data['logistics'][$key]['AcceptStatus'] 	= '已签收';
					}else{
						$data['logistics'][$key]['AcceptStatus'] 	= '运输中';
					}
				}
				array_push($data['logistics'],$logistics);
			}else{
				$data['logistics'][0]							= $logistics;
			}
		}
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 订单退款页面
	 */
	private function orderRefund($Parame){
		//商品数据
		$goods = M('order')->alias('o')
		->field('o.id oid, o.order_no, o_d.gname, o_d.gImg, o_d.avalue, o_d.num, o_d.price')
		->join(array(' LEFT JOIN __ORDER_DESC__ o_d ON o.id = o_d.oid'))
		->where(array('o.id'=>$Parame['id']))
		->select();
		$data = array();
		if (!empty($goods)){
			foreach ($goods as $k=>$v){
				$arr['id']			= $v['oid'];
				$arr['order_no']	= $v['order_no'];
				$arr['sublist'][] 	= $v;
				//总价格
				$totalPrice += $v['price']*$v['num'];
			}
		}
		$data['list'] 		= $arr;
		$data['totalPrice'] = $totalPrice;
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 订单退款提交
	 */
	private function orderRefundSubmit($Parame){
		//用户id
		$uid 					= intval($Parame['uid']);
		//订单id
		$id 					= intval($Parame['id']);
		$count 					= M('refund')->where(array('uid'=>$uid,'oid'=>$id))->count();
		if($count>0){
			return array('Code' =>'101732','Msg'=>$this->Lang['101732']);
		}
		$order 					= M('order')->where(array('id'=>$id))->find();
		//数据
		$data 					= array();
		$data['uid'] 			= $uid;
		$data['oid'] 			= $id;
		$data['doid'] 			= 0;
		$data['gid'] 			= 0;
		$data['money'] 			= $order['total_money'];
		$data['fmoney'] 		= $order['total_money'];
		$data['explain'] 		= $Parame['explain'];
		$data['pic'] 			= $Parame['pic'];
		$data['create_time'] 	= NOW_TIME;
		$res = M('refund')->add($data);
		if($res){
			//改变订单状态
			M('order')->where(array('id'=>$id))->save(array('status'=>5));
			return array('Code' =>'0','Msg'=>$this->Lang['100016']);
		}
		return array('Code' =>'100017','Msg'=>$this->Lang['100017']);
	}
	
	//订单评价
	private function orderEvaluate($Parame){
		//商家id
		$shopId = intval($Parame['shop_id']);
		//评价人id
		$uid	= intval($Parame['uid']);
		//评分
		$score	= intval($Parame['score']);
		//插入表
		M('score')->add(array('shop_id'=>$shopId, 'uid'=>$uid, 'score'=>$score, 'create_time'=>NOW_TIME));
		//求平均数
		$score 	= M('score')->where(array('shop_id'=>$shopId))->avg('score');
		$score	= ceil($score);
		//跟新shop表的字段值
		$res = M('shop')->where(array('id'=>$shopId))->save(array('score'=>$score));
		if($res !== false){
			return array('Code' =>'0','Msg'=>$this->Lang['100016']);
		}
		return array('Code' =>'1','Msg'=>$this->Lang['100017']);
	}
	
}
?>