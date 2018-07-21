<?php
namespace Api\Helper;
/**
 * 订单详情
 * @author 
 */
class CartHelper extends BaseHelper{
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
	
	private function cartList($Parame){
		$uid 			= intval($Parame['uid']);
		//总数
		$count      = M('cart')->where(array('uid' => $uid,'status'=>1))->count();
		$Page       = new \Think\Page($count,10);
		
		$cart = M('cart')
		->field('duoduo_cart.goodsimg,duoduo_cart.goodsname,duoduo_cart.gnum,duoduo_cart.goodsprice,duoduo_cart.id,duoduo_cart.gid,duoduo_goods_attribute.avalue')
		->join('left join duoduo_goods on duoduo_goods.id=duoduo_cart.gid')
		->join('left join duoduo_goods_attribute on duoduo_goods_attribute.id=duoduo_cart.attrid')
		->where(array('duoduo_cart.uid' => $uid,'duoduo_cart.status'=>1))
		->order('duoduo_cart.id desc')
		->limit($Page->firstRow.','.$Page->listRows)
		->select();
		$data['list']			= $cart;
		$data['page']			= $Parame['page'];
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	private function addCart($Parame){
		$uid 			= intval($Parame['uid']);
		$info 			= json_decode($Parame['info'],true);
		//商品id
		$gid 			= intval($info['gid']);
		if(empty($gid)){
			return array('Code' =>'101704','Msg'=>$this->Lang['101704']);
		}
		//商品数量
		$gnum 			= intval($info['num']);
		if($gnum < 1){
			return array('Code' =>'101706','Msg'=>$this->Lang['101706']);
		}
		
		/* $list   	= M('goods_attribute')->field('id,price,stock')->where(array('id' => $attrid))->find();*/
		$cartlist 	= M('cart')->where(array('gid' => $gid, 'uid' => $uid,'status' => 1))->find(); 
		
		$goodsinfo 	= M('goods')->field('id,goodsimg,goodsname,goodsprice,stock')->where(array('id' => $gid))->find();
		if ($goodsinfo['stock'] < 1) {
			return array('Code' =>'101713','Msg'=>$this->Lang['101713']);
		}
		if (!empty($cartlist)) {
			$gnum = $cartlist['gnum'] + $gnum;
			$data = array(
					'gnum' => $gnum,
					'update_time' => NOW_TIME,
			);
			$info = M('cart')->data($data)->where(array('gid' => $gid, 'uid' => $uid))->save();
			if ($info !== false) {
				return array('Code' => '0', 'Msg' =>$this->Lang['101714']);
			} else {
				return array('Code' => '101715', 'Msg' =>$this->Lang['101715']);
			}
		} else {
			$data = array(
					'gid'           => $gid,
					'uid'           => $uid,
					'gnum'          => $gnum,
					'goodsimg'      => $goodsinfo['goodsimg'],
					'goodsprice'    => $goodsinfo['goodsprice'],
					'goodsname'     => $goodsinfo['goodsname'],
					'create_time'   => NOW_TIME,
			);
			$info = M('cart')->data($data)->add();
			if ($info > 0) {
				return array('Code' => '0', 'Msg' =>$this->Lang['101714']);
			} else {
				return array('Code' => '101715', 'Msg' =>$this->Lang['101715']);
			}
		}
		return array('Code' =>'0','Msg'=>$this->Lang['100013']);
	}
	
	private function editCart($Parame){
		//类型
		$type 		= intval($Parame['type']);
		//购物车id
		$cartId		= intval($Parame['cartId']);
		//购物车数量
		$num		= intval($Parame['num']);
		
		if($type == 1){
			if($num < 1){
				return array('Code' => '101717', 'Msg' =>$this->Lang['101717']);
			}
			$attrid 				= M('cart')->where(array('id'=>$cartId))->getField('attrid');
			$goodsAttribute 		= M('goods_attribute')->field('id,stock,price')->where(array('id'=>$attrid))->find();
			if ($goodsAttribute['stock'] < 1 || $num > $goodsAttribute['stock']) {
				return array('Code' => '101713', 'Msg' =>$this->Lang['101713']);
			}
			$data = array(
					'gnum' => $num,
					'update_time' => NOW_TIME,
			);
			$info = M('cart')->data($data)->where(array('id' => $cartId))->save();
			if ($info !== false) {
				return array('Code' => '0', 'Msg' =>$this->Lang['101718']);
			} else {
				return array('Code' => '101719', 'Msg' =>$this->Lang['101719']);
			}
		}elseif($type == 2){
			if (M('cart')->where(array('id' => $cartId))->data(array('status' => 2))->save()) {
				//数据返回
				return array('Code' => '0', 'Msg' =>$this->Lang['101718']);
			} else {
				return array('Code' => '101719', 'Msg' =>$this->Lang['101719']);
			}
		}
	}
	
	/**
	 * 删除购物车
	 */
	private function delCart($Parame){
		$id = intval($Parame['id']);
		if($id <= 0){
			return array('Code' =>'101708','Msg'=>$this->Lang['101708']);
		}
		$res = M('cart')->where(array('id'=>$id))->delete();
		if($res != false){
			return array('Code' =>'0','Msg'=>$this->Lang['100022']);
		}
		return array('Code' =>'100023','Msg'=>$this->Lang['100023']);
	}
}
?>