<?php
namespace Api\Helper;
/**
 * 个人中心
 */
class CenterHelper extends BaseHelper{
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
	 * 个人中心首页
	 */
	private function index($Parame){
		$user = M('user')->where(array('id'=>$Parame['uid']))->find();
		$data = array();
		if(!empty($user)){
			//id
			$data['id'] 				= $user['id'];
			//头像
			$data['avatar'] 			= $user['avatar'];
			//用户名
			$data['nickname'] 			= $user['nickname'];
			//手机号
			$data['phone'] 				= $user['phone'];
			//客服电话
			$data['telephone'] 			= M('platform_config')->where(array('name'=>'MOBILE'))->getField('value');
		}
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 鑫利豆
	 */
	private function xinlidou($Parame){
		$data 			= array();
		//用户id
		$uid 			= intval($Parame['uid']);
		$user = M('user')->field('current_xinlidou')->where(array('id'=>$Parame['uid']))->find();
		$info = array();
		if(!empty($user)){
			$info['total_xinlidou'] 			= $user['current_xinlidou'];
			$info['current_xinlidou'] 			= $user['current_xinlidou'];
			$info['red_packet'] 				= 0;
		}
		$data['info']							= $info;
		
		//总数
		$count      							= M('xinlidou_log')->where(array('uid'=>$uid))->count();
		$Page       							= new \Think\Page($count,10);
		$message 								= M('xinlidou_log')->where(array('uid'=>$uid))->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$row = array();
		if(!empty($message)){
			foreach ($message as $key=>$value){
				$row[$key]['id'] 				= $value['id'];
				$row[$key]['num'] 				= $value['num'];
				$row[$key]['create_time'] 		= date('Y-m-d', $value['create_time']);
				$row[$key]['receive_time'] 		= date('Y-m-d', $value['receive_time']);
			}
		}
		$data['list']							= $row;
		$data['page']							= $Parame['page'];
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 鑫豆库
	 */
	private function xindouku($Parame){
		$data 			= array();
		//用户id
		$uid 			= intval($Parame['uid']);
		//type 1享利豆 2福利豆
		$type 			= intval($Parame['type']);
		
		$user = M('user')->field('current_xianglidou, current_fulidou')->where(array('id'=>$Parame['uid']))->find();
		$info = array();
		if(!empty($user)){
			if($type == 1){
				$info['total_dou'] 				= $user['current_xianglidou'];
				$info['current_dou'] 			= $user['current_xianglidou'];
			}elseif($type == 2){
				$info['total_dou'] 				= $user['current_fulidou'];
				$info['current_dou'] 			= $user['current_fulidou'];
			}
			$t 									= mktime(23,59,59,date('m',NOW_TIME),date('d',NOW_TIME),date('Y',NOW_TIME));
			$count 								= M('red_packet')->where(array('uid'=>$uid,'type'=>$type,'is_receive'=>0,'end_time'=>array('egt',$t)))->count();
			$info['red_packet'] 				= $count;
		}
		$data['info']							= $info;
		
		//总数
		$count      							= M('red_packet')->where(array('uid'=>$uid,'type'=>$type))->count();
		$Page       							= new \Think\Page($count,10);
		$message 								= M('red_packet')->where(array('uid'=>$uid,'type'=>$type))->order('end_time asc,packet_num asc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$row = array();
		if(!empty($message)){
			foreach ($message as $key=>$value){
				$row[$key]['id'] 				= $value['id'];
				$row[$key]['create_time'] 		= date('Ymd', $value['create_time']).'-'.$value['packet_num'];
				$row[$key]['end_time'] 			= date('Ymd', $value['end_time']).'-'.$value['packet_num'];
				if(!empty($value['receive_time'])){
					$row[$key]['num'] 			= $value['num'];
					$row[$key]['receive_time'] 	= date('Ymd', $value['receive_time']).'-'.$value['packet_num'];
				}else{
					$row[$key]['num'] 			= '';
					$row[$key]['receive_time'] 	= '';
				}
				//判断是否失效
				if($value['end_time'] < NOW_TIME && $value['is_receive'] == 0){
					$row[$key]['invalid'] 		= 1;	//失效
				}else{
					$row[$key]['invalid'] 		= 0;	//未失效
				}
			}
		}
		$data['list']							= $row;
		$data['page']							= $Parame['page'];
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 个人信息
	 */
	private function manager($Parame){
		$user = M('user')->where(array('id'=>$Parame['uid']))->find();
		$data = array();
		if(!empty($user)){
			$data['avatar'] 		= (string)$user['avatar'];
			$data['nickname'] 		= (string)$user['nickname'];
			$data['phone'] 			= (string)$user['phone'];
		}
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 个人信息修改
	 */
	private function managerSubmit($Parame){
		$data 				= array();
		$data['nickname'] 	= $Parame['nickname'];
		$data['phone'] 		= $Parame['phone'];
		$res = M('user')->where(array('id'=>$Parame['uid']))->save($data);
		if($res !== false){
			return array('Code' =>'0','Msg'=>$this->Lang['100010']);
		}
		return array('Code' =>'100011','Msg'=>$this->Lang['100011']);
	}
	
	
	/**
	 * 我的收藏列表
	 */
	private function goodsCollectionList($Parame){
		$page						= $Parame['page'];
		$limit						= 10;
		//获取数据
		$MainTab					= 'collection';
		$MainAlias					= 'main';
		$MainField					= array('id,gid');
		//主表模型
		$MainModel 					= M($MainTab)->alias($MainAlias);
		$RelationTab				= array();
		$RelationTab				= $this->getRelationTab($RelationTab);
		$tables	  					= $RelationTab['tables'];
		$RelationFields				= $RelationTab['fields'];
		$model						= !empty($tables) ? $MainModel->join ( $tables ,'LEFT'): $MainModel;
		//检索条件
		$map 						= array();
		$map['main.uid']			= $Parame['uid'];
		//1收藏 2浏览历史
		$map['main.type']			= 1;
		$map['main.status']			= 1;
		//排序
		$order 						= $MainAlias.'.id desc';
		//检索字段
		$fields						= (empty($MainField) ? get_fields_string($MainModel->getDbFields(),$MainAlias).',' : get_fields_string($MainField,$MainAlias).',') . $RelationFields;
		$fields						= trim($fields,',');
		//列表数据
		$list 						= $this->getLists($model,$map,$order,$fields,$page,$limit,false);
		if (!empty($list)){
			foreach ($list as $k=>$v){
				$goods 					= M('goods')->field('id,goodsname,goodsimg')->where(array('id'=>$v['gid']))->find();
				$row[$k]['id'] 			= $v['gid'];
				$row[$k]['goodsname'] 	= $goods['goodsname'];
				$row[$k]['goodsimg'] 	= $goods['goodsimg'];
			}
		}
		$data['list']			= empty($row) ? array() : $row;
		$data['page']			= $Parame['page'];
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	
	/**
	 * 浏览历史列表
	 */
	private function goodsHistoryList($Parame){
		$page						= $Parame['page'];
		$limit						= 10;
		//获取数据
		$MainTab					= 'collection';
		$MainAlias					= 'main';
		$MainField					= array('id,gid');
		//主表模型
		$MainModel 					= M($MainTab)->alias($MainAlias);
		$RelationTab				= array();
		$RelationTab				= $this->getRelationTab($RelationTab);
		$tables	  					= $RelationTab['tables'];
		$RelationFields				= $RelationTab['fields'];
		$model						= !empty($tables) ? $MainModel->join ( $tables ,'LEFT'): $MainModel;
		//检索条件
		$map 						= array();
		$map['main.uid']			= $Parame['uid'];
		//1收藏 2浏览历史
		$map['main.type']			= 2;
		//排序
		$order 						= $MainAlias.'.create_time desc';
		//检索字段
		$fields						= (empty($MainField) ? get_fields_string($MainModel->getDbFields(),$MainAlias).',' : get_fields_string($MainField,$MainAlias).',') . $RelationFields;
		$fields						= trim($fields,',');
		//列表数据
		$list 						= $this->getLists($model,$map,$order,$fields,$page,$limit,false);
		if (!empty($list)){
			foreach ($list as $k=>$v){
				$goods 					= M('goods')->field('id,goodsname,goodsimg')->where(array('id'=>$v['gid']))->find();
				$row[$k]['id'] 			= $v['gid'];
				$row[$k]['goodsname'] 	= $goods['goodsname'];
				$row[$k]['goodsimg'] 	= $goods['goodsimg'];
			}
		}
		$data['list']			= empty($row) ? array() : $row;
		$data['page']			= $Parame['page'];
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	
	/**
	 * 添加和修改地址
	 */
	private function editAddress($Parame){
		//id
		$id 					= intval($Parame['id']);
		//数据
		$data 					= array();
		$data['uid'] 			= $Parame['uid'];
		$data['name'] 			= $Parame['name'];
		$data['phone'] 			= $Parame['phone'];
		$data['province'] 		= $Parame['province'];
		$data['city'] 			= $Parame['city'];
		$data['county'] 		= $Parame['county'];
		$data['address'] 		= $Parame['address'];
		if($id>0){
			$data['update_time'] 	= NOW_TIME;
			$res = M('address')->where(array('id'=>$id))->save($data);
			if($res !== false){
				return array('Code' =>'0','Msg'=>$this->Lang['100010']);
			}
		}else{
			$data['create_time'] 	= NOW_TIME;
			$res = M('address')->add($data);
			if($res){
				return array('Code' =>'0','Msg'=>$this->Lang['100016']);
			}
		}
		return array('Code' =>'100017','Msg'=>$this->Lang['100017']);
	}
	
	/**
	 * 删除地址
	 */
	private function delAddress($Parame){
		$id = intval($Parame['id']);
		if($id <= 0){
			return array('Code' =>'100088','Msg'=>$this->Lang['100088']);
		}
		$res = M('address')->where(array('id'=>$id))->delete();
		if($res != false){
			return array('Code' =>'0','Msg'=>$this->Lang['100022']);
		}
		return array('Code' =>'100023','Msg'=>$this->Lang['100023']);
	}
	
	/**
	 * 设置默认地址
	 */
	private function setAddress($Parame){
		$id 	= intval($Parame['id']);
		$uid 	= intval($Parame['uid']);
		if($id <= 0){
			return array('Code' =>'100088','Msg'=>$this->Lang['100088']);
		}
		M('address')->where(array('uid'=>$uid))->save(array('is_selected'=>0));
		$res = M('address')->where(array('uid'=>$uid, 'id'=>$id))->save(array('is_selected'=>1));
		if($res != false){
			return array('Code' =>'0','Msg'=>$this->Lang['100010']);
		}
		return array('Code' =>'100011','Msg'=>$this->Lang['100011']);
	}
	
	/**
	 * 地址列表
	 */
	private function addressList($Parame){
		$uid 			= intval($Parame['uid']);
		//总数
		$count      	= M('shop')->where(array('status'=>1))->count();
		$Page       	= new \Think\Page($count,10);
		$message 		= M('address')->where(array('status'=>1))->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$data = $row = array();
		if(!empty($message)){
			foreach ($message as $key=>$value){
				$row[$key]['id'] 			= $value['id'];
				$row[$key]['shop_name'] 		= $value['name'];
				/* $row[$key]['province'] 		= $value['province'];
				$row[$key]['city'] 			= $value['city'];
				$row[$key]['county'] 		= $value['county']; */
				$row[$key]['address'] 		= $value['address'];
			}
		}
		$data['list']			= $row;
		$data['page']			= $Parame['page'];
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 获取招聘区域
	 */
	private function jobCounty($Parame){
		$pid 		= M('area')->where(array('area'=>$Parame['city']))->getField('id');
		$data 		= M('area')->field('id,area')->where(array('pid'=>$pid))->select();
		if(empty($data)){
			$data 	= array();
		}
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 发布招聘
	 */
	private function addJob($Parame){
		//数据
		$data 					= array();
		$data['uid'] 			= $Parame['uid'];
		$data['county'] 		= $Parame['county'];
		$data['end_time'] 		= strtotime($Parame['end_time']);
		$data['company'] 		= $Parame['company'];
		$data['company_logo'] 	= $Parame['company_logo'];
		$data['address'] 		= $Parame['address'];
		$data['position'] 		= $Parame['position'];
		$data['number'] 		= $Parame['number'];
		$data['wages'] 			= $Parame['wages'];
		$data['content'] 		= $Parame['content'];
		$data['status'] 		= 1;
		$data['create_time'] 	= NOW_TIME;
		$res = M('job')->add($data);
		if($res){
			return array('Code' =>'0','Msg'=>$this->Lang['100016']);
		}else{
			return array('Code' =>'100017','Msg'=>$this->Lang['100017']);
		}
	}
	
	/**
	 * 我的招聘
	 */
	private function userJob($Parame){
		$uid 			= intval($Parame['uid']);
		//总数
		$count      	= M('job')->where(array('uid'=>$uid))->count();
		$Page       	= new \Think\Page($count,10);
		$job 			= M('job')->where(array('uid'=>$uid))->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$data = $row = array();
		if(!empty($job)){
			foreach ($job as $key=>$value){
				$row[$key]['id'] 				= $value['id'];
				$row[$key]['position'] 			= $value['position'];
				$areaIdArr 						= explode(',', $value['county']);
				$areaId							= $areaIdArr['0'];
				if(count($areaIdArr)>1){
					$areaId = M('area')->where(array('id'=>$areaId))->getField('pid');
				}
				$row[$key]['county'] 			= M('area')->where(array('id'=>$areaId))->getField('area');
				$row[$key]['number'] 			= $value['number'];
				$row[$key]['create_time'] 		= date('m月d日',$value['create_time']);
				$row[$key]['company_logo'] 		= $value['company_logo'];
				$row[$key]['company'] 			= $value['company'];
				$row[$key]['wages'] 			= $value['wages'];
			}
		}
		$data['list']			= $row;
		$data['page']			= $Parame['page'];
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 招聘详情
	 */
	private function jobDetail($Parame){
		$job 	= M('job')->where(array('id'=>$Parame['id']))->find();
		$data 	= array();
		if(!empty($job)){
			$shopId 				= M('shop')->where(array('uid'=>$job['uid']))->getField('id');
			if(empty($shopId)){
				$shopId 			= '';
			}
			$data['shop_id'] 		= $shopId;
			$data['position'] 		= $job['position'];
			$areaIdArr 				= explode(',', $job['county']);
			$areaId					= $areaIdArr['0'];
			if(count($areaIdArr)>1){
				$areaId = M('area')->where(array('id'=>$areaId))->getField('pid');
			}
			$data['county'] 		= M('area')->where(array('id'=>$areaId))->getField('area');
			$data['number'] 		= $job['number'];
			$data['create_time'] 	= date('m月d日',$job['create_time']);
			$data['wages'] 			= $job['wages'];
			$data['address'] 		= $job['address'];
			$data['company'] 		= $job['company'];
			$data['content'] 		= $job['content'];
		}
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 求才讯息
	 */
	private function jobList($Parame){
		$where 					= array();
		$uid 					= intval($Parame['uid']);
		$where['status']		= 2;
		$area 					= intval($Parame['area']);
		if(!empty($area)){
			$where['_string']	= "FIND_IN_SET({$area}, county)";
		}
		//总数
		$count      			= M('job')->where($where)->count();
		$Page       			= new \Think\Page($count,10);
		$job 					= M('job')->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		
		$data = $row = array();
		if(!empty($job)){
			foreach ($job as $key=>$value){
				$row[$key]['id'] 				= $value['id'];
				$row[$key]['position'] 			= $value['position'];
				$areaIdArr 						= explode(',', $value['county']);
				$areaId							= $areaIdArr['0'];
				if(count($areaIdArr)>1){
					$areaId = M('area')->where(array('id'=>$areaId))->getField('pid');
				}
				$row[$key]['county'] 			= M('area')->where(array('id'=>$areaId))->getField('area');
				$row[$key]['number'] 			= $value['number'];
				$row[$key]['create_time'] 		= date('m月d日',$value['create_time']);
				$row[$key]['company_logo'] 		= $value['company_logo'];
				$row[$key]['company'] 			= $value['company'];
				$row[$key]['wages'] 			= $value['wages'];
			}
		}
		$data['list']			= $row;
		$data['page']			= $Parame['page'];
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 获取视频单价
	 */
	private function getVideoPrice($Parame){
		//人数
		$num 							= intval($Parame['num']);
		//时长
		$videoTime 						= intval($Parame['video_time']);
		$where							= array();
		$where['time']					= $videoTime;
		$where['least_num']				= array('ELT',$num);
		$where['most_num']				= array('EGT',$num);
		$price 							= M('video_price')->where($where)->getField('price');
		if(empty($price)){
			$price						= '';
		}
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>array('price'=>$price));
	}
	
	/**
	 * 发布商家视频
	 */
	private function addVideo($Parame){
		//数据
		$data 							= array();
		$data['order_no'] 				= create_orderid();
		$data['out_trade_no'] 			= 'REWORD'.date('YmdHis',NOW_TIME).randomString('6',0);
		$data['uid'] 					= $Parame['uid'];
		$data['num'] 					= $Parame['num'];
		$data['price'] 					= $Parame['price'];
		$data['show_time'] 				= strtotime($Parame['show_time']);
		$data['video_time'] 			= $Parame['video_time'];
		$data['business_telephone'] 	= $Parame['business_telephone'];
		$data['address'] 				= $Parame['address'];
		$data['title'] 					= $Parame['title'];
		$data['content'] 				= $Parame['content'];
		$data['total_money'] 			= $Parame['num']*$Parame['price'];
		$data['pay_status'] 			= 0;
		$data['check_status'] 			= 1;
		$data['create_time'] 			= NOW_TIME;
		$res = M('video')->add($data);
		if($res){
			return array('Code' =>'0','Msg'=>$this->Lang['100016']);
		}else{
			return array('Code' =>'100017','Msg'=>$this->Lang['100017']);
		}
	}
	
	/**
	 * 获取广告信息
	 */
	private function getAdvertisementInfo($Parame){
		
		$shop_id 				= intval($Parame['shop_id']);
		$uid 					= intval($Parame['uid']);
		$mode					= $Parame['mode'];
		$show_time				= strtotime($Parame['show_time']);
		//查看店铺的经纬度
		$shop = M('shop')->field('longitude, latitude')->where(array('id'=>$shop_id))->find();
		
		M('red_packet')->where(array('packet_date'=>$show_time, 'order_no'=>1, 'is_receive'=>0))->save(array('order_no'=>0));
		$redPacket = M('red_packet')->field('uid')->where(array('packet_date'=>$show_time, 'order_no'=>0, 'is_receive'=>0))->group('uid')->select();
		if(!empty($redPacket)){
			foreach ($redPacket as $key=>$value){
				$user = M('user')->field('longitude, latitude')->where(array('id'=>$value['uid']))->find();
				$distance = getDistanceBetweenPointsNew($shop['latitude'], $shop['longitude'], $user['latitude'], $user['longitude']);
				if($distance['meters'] > $mode){
					unset($redPacket[$key]);
				}
			}
			if(!empty($redPacket)){
				//标记用户
				foreach ($redPacket as $k=>$v) {
					$row = M('red_packet')->field('id')->where(array('packet_date'=>$show_time, 'order_no'=>0, 'is_receive'=>0, 'uid'=>$v['uid']))->order('create_time asc')->find();
					$id = $row['id'];
					M('red_packet')->where(array('id'=>$id))->save(array('order_no'=>1));
				}
			}
		}
		$num 							= count($redPacket);
		if(empty($num)){
			$num						= '';
		}
		$where							= array();
		$where['least_num']				= array('ELT',$num);
		$where['most_num']				= array('EGT',$num);
		$price 							= M('advertisement_price')->where($where)->getField('price');
		if(empty($price)){
			$price						= '';
		}
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>array('num'=>$num,'price'=>$price));
	}
	
	/**
	 * 发布红包广告
	 */
	private function addAdvertisement($Parame){
		//数据
		$data 							= array();
		$data['order_no'] 				= create_orderid();
		$data['out_trade_no'] 			= 'REWORD'.date('YmdHis',NOW_TIME).randomString('6',0);
		$data['uid'] 					= $Parame['uid'];
		$data['shop_id'] 				= $Parame['shop_id'];
		$data['mode'] 					= $Parame['mode'];
		$data['show_time'] 				= strtotime($Parame['show_time']);
		$data['num'] 					= $Parame['num'];
		$data['price'] 					= $Parame['price'];
		$data['design_type'] 			= $Parame['design_type'];
		if($Parame['design_type'] == 1){		//委托平台设计
			$data['content'] 			= $Parame['content'];
			$data['img'] 				= '';
		}elseif($Parame['design_type'] == 2){	//自行设计
			$data['content'] 			= $Parame['content'];
			$data['img'] 				= $Parame['content'];
		}
		$data['total_money'] 			= $Parame['num']*$Parame['price'];
		$data['pay_status'] 			= 0;
		$data['check_status'] 			= 1;
		$data['create_time'] 			= NOW_TIME;
		$res 							= M('advertisement')->add($data);
		if($res){
			//红包
			M('red_packet')->where(array('packet_date'=>$data['show_time'], 'order_no'=>1, 'is_receive'=>0))->save(array('order_no'=>$data['order_no']));
			return array('Code' =>'0','Msg'=>$this->Lang['100016']);
		}else{
			return array('Code' =>'100017','Msg'=>$this->Lang['100017']);
		}
	}
	
	/**
	 * 历史消息
	 */
	private function historyList($Parame){
		$where 							= array();
		//$where['check_status']			= 2;
		//总数
		$count      					= M('advertisement')->where($where)->count();
		$Page       					= new \Think\Page($count,10);
		$promotion 						= M('advertisement')->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$row1 = array();
		if(!empty($promotion)){
			foreach ($promotion as $key=>$value){
				$row1[$key]['id'] 				= $value['id'];
				$row1[$key]['type'] 			= 1;
				$row1[$key]['num'] 				= $value['num'];
				$row1[$key]['video_time'] 		= '';
				$row1[$key]['mode'] 			= $value['mode'];
				if($value['design_type'] == 1){
					$row1[$key]['design'] 		= '委托平台设计';
					$row1[$key]['content'] 		= '';
				}
				if($value['design_type'] == 2){
					$row1[$key]['design'] 		= '自行设计';
					$row1[$key]['content'] 		= $value['content'];
				}
				$row1[$key]['show_time'] 		= date('Y-m-d', $value['show_time']);
			}
		}
		
		$count      						= M('video')->where($where)->count();
		$Page       						= new \Think\Page($count,10);
		$promotion 							= M('video')->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$row2 = array();
		if(!empty($promotion)){
			foreach ($promotion as $key=>$value){
				$row2[$key]['id'] 			= $value['id'];
				$row2[$key]['type'] 		= 2;
				$row2[$key]['num'] 			= $value['num'];
				$row2[$key]['video_time'] 	= $value['video_time'];
				$row2[$key]['mode'] 		= '';
				$row2[$key]['design'] 		= '';
				$row2[$key]['content'] 		= $value['content'];
				$row2[$key]['show_time'] 	= date('Y-m-d', $value['show_time']);
			}
		}
		$data['list']					= array_merge($row1,$row2);
		$data['page']					= $Parame['page'];
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 推广订单详情
	 */
	private function spreadDetail($Parame){
		//用户
		$uid 			= intval($Parame['uid']);
		if($Parame['type'] == 1){
			//让利推广
			$advertisement 	= M('advertisement')->where(array('uid'=>$uid))->order('id desc')->find();
			if(!empty($advertisement)){
				if($advertisement['pay_status'] == 0){
					$data['id'] 					= $advertisement['id'];
					$data['type_id'] 				= $Parame['type'];
					$data['type'] 					= '消费让利推广';
					$data['shop_id'] 				= $advertisement['shop_id'];
					$data['mode'] 					= $advertisement['mode'];
					$data['show_time'] 				= date('Y-m-d', $advertisement['show_time']);
					$data['num'] 					= $advertisement['num'];
					$data['price'] 					= $advertisement['price'];
					if($advertisement['design_type'] == 1){
						$data['design_name'] 		= '委托平台设计';
						$data['content'] 			= $advertisement['content'];
					}elseif($advertisement['design_type'] == 2){
						$data['design_name'] 		= '自行设计';
						$data['content'] 			= '已上传';
					}
					$data['total_money'] 			= $advertisement['total_money'];
					return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
				}
			}
		}elseif($Parame['type'] == 2){
			//视频
			$video 	= M('video')->where(array('uid'=>$uid))->find();
			if(!empty($video)){
				if($video['pay_status'] == 0){
					$data['id'] 					= $video['id'];
					$data['type_id'] 				= $Parame['type'];
					$data['type'] 					= '商家视频';
					$data['num'] 					= $video['num'];
					$data['price'] 					= $video['price'];
					$data['show_time'] 				= date('Y-m-d', $advertisement['show_time']);
					$data['video_time'] 			= $video['video_time'];
					$data['business_telephone'] 	= $video['business_telephone'];
					$data['address'] 				= $video['address'];
					$data['content'] 				= '已上传';
					$data['title'] 					= $video['title'];
					$data['total_money'] 			= $video['total_money'];
					return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
				}
			}
		}
		return array('Code' =>'101827','Msg'=>$this->Lang['101827']);
	}
	
	/**
	 * 推广订单状态
	 */
	private function spreadStatus($Parame){
		//用户
		$uid 			= intval($Parame['uid']);
		//让利推广
		$advertisement 	= M('advertisement')->where(array('uid'=>$uid))->order('id desc')->find();
		if(!empty($advertisement)){
			if($advertisement['check_status'] == 1 || $advertisement['check_status'] == 3){
				return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>array('status'=>$advertisement['check_status'],'type'=>1));
			}
			if($advertisement['pay_status'] == 0){
				return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>array('status'=>'4','type'=>1));
			}
		}
		//视频
		$video 	= M('video')->where(array('uid'=>$uid))->find();
		if(!empty($video)){
			if($video['check_status'] == 1 || $video['check_status'] == 3){
				return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>array('status'=>$video['check_status'],'type'=>2));
			}
			if($video['pay_status'] == 0){
				return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>array('status'=>'4','type'=>2));
			}
		}
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>array('status'=>'2','type'=>0));
	}
	
	/**
	 * 提交
	 */
	private function adOrderSubmit($Parame){
		$id 		= intval($Parame['id']);
		//用户id
		$uid 		= intval($Parame['uid']);
		$user 		= M('user')->field('current_xinlidou,current_xianglidou')->where(array('id'=>$uid))->find();
		//订单类型【1让利推广 2商家视频】
		$type 		= intval($Parame['type_id']);
		//鑫利豆
		$xinlidou 	= $Parame['xinlidou'];
		if($xinlidou != ''){
			if(!judge_decimal($xinlidou, 0)){
				return array('Code' =>'1','Msg'=>'鑫利豆格式错误');
			}
			if($xinlidou > $user['current_xinlidou']){
				return array('Code' =>'1','Msg'=>'鑫利豆不能大于你现有的鑫利豆');
			}
		}else{
			$xinlidou = 0;
		}
		//享利豆
		$xianglidou = $Parame['xianglidou'];
		if($xianglidou != ''){
			if(!judge_decimal($xianglidou, 0)){
				return array('Code' =>'1','Msg'=>'享利豆格式错误');
			}
			if($xianglidou > $user['current_xianglidou']){
				return array('Code' =>'1','Msg'=>'享利豆不能大于你现有的享利豆');
			}
		}else{
			$xianglidou = 0;
		}
		if($type == 1){
			$totalMoney = M('advertisement')->where(array('id'=>$id))->getField('total_money');
			if(($xinlidou+$xianglidou) >= $totalMoney){
				return array('Code' =>'1','Msg'=>'你使用的豆子不能超过订单的总金额');
			}
			$data = array(
					'xinlidou' => $xinlidou,
					'xianglidou' => $xianglidou,
					'pay_money' => $totalMoney-$xinlidou-$xianglidou
			);
			M('advertisement')->where(array('id'=>$id))->save($data);
			return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>array('oid'=>$id, 'money'=>$data['pay_money'], 'table'=>'advertisement'));
		}elseif($type == 2){
			$totalMoney = M('video')->where(array('id'=>$id))->getField('total_money');
			if(($xinlidou+$xianglidou) >= $totalMoney){
				return array('Code' =>'1','Msg'=>'你使用的豆子不能超过订单的总金额');
			}
			$data = array(
					'xinlidou' => $xinlidou,
					'xianglidou' => $xianglidou,
					'pay_money' => $totalMoney-$xinlidou-$xianglidou
			);
			M('video')->where(array('id'=>$id))->save($data);
			return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>array('oid'=>$id, 'money'=>$data['pay_money'], 'table'=>'video'));
		}
	}
	
	/**
	 * 视频详情
	 */
	private function videoDetail($Parame){
		$promotion 	= M('video')->where(array('id'=>$Parame['id']))->find();
		$data 		= array();
		if(!empty($promotion)){
			$data['id'] 					= $promotion['id'];
			$data['content'] 				= $promotion['content'];
			$data['title'] 					= $promotion['title'];
			$data['business_telephone'] 	= $promotion['business_telephone'];
			$data['address'] 				= $promotion['address'];
		}
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 看视频获取豆子
	 */
	private function getXindou($Parame){
		//用户id
		$uid 						= intval($Parame['uid']);
		//视频id
		$id 						= intval($Parame['id']);
		//判断一个视频只能领取一次
		$count = M('video_xindou_log')->where(array('uid'=>$uid, 'video_id'=>$id))->count();
		if($count){
			return array('Code' =>'1','Msg'=>'你已领取豆子,不能重复领取');
		}
		$video 						= M('video')->field('get_xinlidou, get_fulidou')->where(array('id'=>$id))->find();
		if(empty($video['get_xinlidou'])){
			$xinlidou 				= 0;
		}else{
			$xinlidou 				= $video['get_xinlidou'];
		}
		if(empty($video['get_fulidou'])){
			$fulidou 				= 0;
		}else{
			$fulidou 				= $video['get_fulidou'];
		}
		$xindou						= $xinlidou+$fulidou;
		//数据
		$row 						= array();
		$row['total_xinlidou'] 		= array('exp',"total_xinlidou+{$xinlidou}");
		$row['current_xinlidou'] 	= array('exp',"current_xinlidou+{$xinlidou}");
		$row['total_fulidou'] 		= array('exp',"total_fulidou+{$fulidou}");
		$row['current_fulidou'] 	= array('exp',"current_fulidou+{$fulidou}");
		$res = M('user')->where(array('id'=>$uid))->save($row);
		if($res !== false){
			//插入视频鑫豆记录
			M('video_xindou_log')->add(array(
					'uid' => $uid,
					'video_id' => $id,
					'xinlidou' => $xinlidou,
					'fulidou' => $fulidou,
					'create_time' => NOW_TIME,
			));
			return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>array('num'=>$xindou));
		}
		return array('Code' =>'100019','Msg'=>$this->Lang['100019']);
	}
	
	/**
	 * 视频列表
	 */
	private function videoList($Parame){
		$where 							= array();
		$where['check_status']			= 2;
		//总数
		$count      					= M('video')->where($where)->count();
		$Page       					= new \Think\Page($count,10);
		$promotion 						= M('video')->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
	
		$data = $row = array();
		if(!empty($promotion)){
			foreach ($promotion as $key=>$value){
				$row[$key]['id'] 		= $value['id'];
				$row[$key]['content'] 	= $value['content'];
				$row[$key]['title'] 	= $value['title'];
			}
		}
		$data['list']					= $row;
		$data['page']					= $Parame['page'];
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 银行卡列表
	 */
	private function bankList($Parame){
		$where 									= array();
		$where['uid']							= intval($Parame['uid']);
		//总数
		$count      							= M('bank_card')->where($where)->count();
		$Page       							= new \Think\Page($count,10);
		
		$bankCard = M('bank_card')->where($where)->order('id asc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$data = $row = array();
		if(!empty($bankCard)){
			foreach ($bankCard as $key=>$value){
				//id
				$row[$key]['id'] 					= $value['id'];
				//开户银行
				$row[$key]['bank_name'] 			= $value['bank_name'];
				//开户账号
				$row[$key]['card_number'] 			= $value['card_number'];
				//银行logo
				$row[$key]['card_logo'] 			= $value['card_logo'];
				$row[$key]['card_bank_logo'] 		= 'http://'.WEB_DOMAIN.'/Public/Images/bank/logo/'.$value['card_logo'].'.png';
				$row[$key]['card_bank_background'] 	= 'http://'.WEB_DOMAIN.'/Public/Images/bank/bg/'.$value['card_logo'].'.png';
			}
		}
		$data['list']							= $row;
		$data['page']							= $Parame['page'];
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 银行卡列表
	 */
	private function bankType($Parame){
		$bankAbbreviate = M('bank_abbreviate')->field('id,name')->where(array('is_del'=>0))->order('id asc')->select();
		$data = array();
		if(!empty($bankAbbreviate)){
			foreach ($bankAbbreviate as $key=>$value){
				//id
				$data[$key]['id'] 				= $value['id'];
				//开户银行
				$data[$key]['name'] 			= $value['name'];
			}
		}
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 添加银行卡
	 */
	private function addBank($Parame){
		//银行信息
		$bankAbbreviate 		= M('bank_abbreviate')->where(array('id'=>$Parame['bank_id']))->find();
		//数据
		$data 					= array();
		$data['uid'] 			= $Parame['uid'];
		$data['bank_name'] 		= (string)$bankAbbreviate['name'];
		$data['card_logo'] 		= (string)$bankAbbreviate['abbreviate'];
		$data['card_number'] 	= $Parame['card_number'];
		$data['phone'] 			= $Parame['phone'];
		$data['id_card'] 		= $Parame['ID_card'];
		
		/* $cardInfo = CurlHttp('http://detectionBankCard.api.juhe.cn/bankCard?key='.C('JH_BANK_KEY').'&cardid='.$Parame['card_number']);
		$cardInfo = json_decode($cardInfo, true);
		if($cardInfo['error_code'] == 0){
			$data['bank_name'] 		= $cardInfo['result']['bank'];
			$data['card_logo'] 		= ltrim(strrchr($cardInfo['result']['logo'], '/'),'/');
		} */
		//银行卡是否绑定
		$count = M('bank_card')->where(array('uid'=>$Parame['uid'], 'card_number'=>$Parame['card_number']))->count();
		if($count > 0){
			return array('Code' =>'101303','Msg'=>$this->Lang['101303']);
		}else{
			$res = M('bank_card')->add($data);
			if($res){
				return array('Code' =>'0','Msg'=>$this->Lang['100016']);
			}
		}
		return array('Code' =>'100017','Msg'=>$this->Lang['100017']);
	}
	
	/**
	 * 删除银行卡
	 */
	private function delBank($Parame){
		$id = intval($Parame['id']);
		if($id <= 0){
			return array('Code' =>'101304','Msg'=>$this->Lang['101304']);
		}
		$res = M('bank_card')->where(array('id'=>$id))->delete();
		if($res){
			return array('Code' =>'0','Msg'=>$this->Lang['101305']);
		}
		return array('Code' =>'101306','Msg'=>$this->Lang['101306']);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/**
	 * 交易记录
	 */
	private function transactionRecord($Parame){
		
		
		$page						= $Parame['page'];
		$limit						= 10;
		//获取数据
		$MainTab					= 'transaction_log';
		$MainAlias					= 'main';
		$MainField					= array('id,uid,description,type,symbol,money,create_time');
			
		//主表模型
		$MainModel 					= M($MainTab)->alias($MainAlias);
			
		$RelationTab				= array();
		$RelationTab				= $this->getRelationTab($RelationTab);
		$tables	  					= $RelationTab['tables'];
		$RelationFields				= $RelationTab['fields'];
		$model						= !empty($tables) ? $MainModel->join ( $tables ,'LEFT'): $MainModel;
			
		//检索条件
		$map 						= array();
		$map['main.uid']			= $Parame['uid'];
		if($Parame['type']>0){
			$map['type'] 			= $Parame['type'];
		}
		//排序
		$order						= $MainAlias.'.create_time desc';
		//检索字段
		$fields						= (empty($MainField) ? get_fields_string($MainModel->getDbFields(),$MainAlias).',' : get_fields_string($MainField,$MainAlias).',') . $RelationFields;
		$fields						= trim($fields,',');
			
		//列表数据
		$transactionLog 			= $this->getLists($model,$map,$order,$fields,$page,$limit,false);
		
		$list = array();
		if(!empty($transactionLog)){
			foreach ($transactionLog as $key=>$value){
				//交易描述
				$list[$key]['description'] 		= $value['description'];
				//时间
				$list[$key]['create_time'] 		= date('Y.m.d H:i', $value['create_time']);
				//1支出 2收入
				$list[$key]['type'] 			= $value['type'];
				//金额
				$list[$key]['money'] 			= $value['symbol'].$value['money'];
			}
		}
		
		$data['list']			= empty($list) ? array() : $list;
		$data['page']			= $Parame['page'];
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 客服中心
	 */
	private function service($Parame){
		$service = M('service')->order('id asc')->select();
		$data = array();
		if(!empty($service)){
			foreach ($service as $key=>$value){
				//qq
				$data[$key]['qq'] 		= $value['qq'];
			}
		}
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 服务调查
	 */
	private function addFeedback($Parame){
		$data 					= array();
		$data['uid'] 			= $Parame['uid'];
		$data['content'] 		= $Parame['content'];
		$data['create_time'] 	= NOW_TIME;
	
		$res = M('feedback')->add($data);
		if($res){
			return array('Code' =>'0','Msg'=>$this->Lang['100016']);
		}
		return array('Code' =>'1','Msg'=>$this->Lang['100011']);
	}
	
	/**
	 * 关于我们
	 */
	private function about($Parame){
		$about = M('about')->order('id desc')->find();
		$data = array();
		if(!empty($about)){
			$data['pic'] 		= $about['pic'];
			$data['name'] 		= $about['name'];
			$data['content'] 	= $about['content'];
		}
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
}
?>