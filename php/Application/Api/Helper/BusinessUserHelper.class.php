<?php
namespace Api\Helper;
use User\Api\UserApi;
/**
 * 
 * @author
 */
class BusinessUserHelper extends BaseHelper{
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
	 * 商家中心首页
	 */
	private function index($Parame){
		
		//店铺信息
		$shop = M('shop')->where(array('uid'=>$Parame['uid']))->find();
		$pay_img = 'http://'.WEB_DOMAIN.'/Uploads/Attachment/'.$shop['id'].'.png';
		if(!is_file($pay_img)){
			if(empty($shop['code_url'])){
				//获取access_token
				$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".C('GZH.APPID')."&secret=".C('GZH.KEY');
				$access_token_info = CurlHttp($url);
				$access_token_arr = json_decode($access_token_info, true);
				$access_token = $access_token_arr['access_token'];
				//获取ticket
				$url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$access_token;
				$body = array();
				$body['action_name'] = 'QR_LIMIT_SCENE';
				$body['action_info'] = array('scene'=>array('scene_id'=>$shop['id']));
				$data_string =  json_encode($body);
				$ticket_info = CurlHttp($url, $data_string, 'POST');
				$ticket_arr = json_decode($ticket_info, true);
				M('shop')->where(array('id'=>$shop['id']))->save(array('code_url'=>$ticket_arr['url']));
				$code_url = $ticket_arr['url'];
			}else{
				$code_url = $shop['code_url'];
			}
			Vendor('Qrcode.QRcode');
			\QRcode::png($code_url, 'Uploads/Attachment/'.$shop['id'].'.png', 'QR_ECLEVEL_L', 10);
			$pay_img = 'http://'.WEB_DOMAIN.'/Uploads/Attachment/'.$shop['id'].'.png';
		}
		
		//shop_id
		$data['id'] 				= $shop['id'];
		//广告
		$data['ad_img'] 			= 'http://onethinktest.u.qiniudn.com/1499931771jyxmEN.jpg';
		//收款码
		$data['pay_img'] 			= $pay_img;
		//提拨比例
		$grade 						= $shop['grade'];
		$proportion 				= M('shop_proportion')->where(array('grade'=>$grade))->getField('proportion');
		$data['proportion'] 		= $proportion;
		
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 商家申请入驻接口
	 */
	private function apply($Parame){
		//密码
		$phone 				= $Parame['phone'];
		$password			= md5($Parame['password']);
		//邀请码
		$code				= $Parame['code'];
		
		$platform_code 		= M('platform_config')->where(array('name'=>'CODE'))->getField('value');
		if($code == $platform_code){	//平台邀请码
			$pid			= 0;
		}else{
			$pid 			= M('user')->where(array('code'=>strtoupper($code)))->getField('id');
			if($pid < 1){
				return array('Code' =>'1','Msg'=>'邀请码不存在,请填写正确的邀请码');
			}
		}
		
		//用户信息
		$userData = array(
				'type'					=> 2,
				'phone'					=> $phone,
				'password' 				=> $password,
				'pid' 					=> $pid,
				'create_time' 			=> NOW_TIME,
				'account_person' 		=> $Parame['account_person'],
				'bank_account' 			=> $Parame['bank_account'],
				'certificates_type' 	=> $Parame['certificates_type'],
				'document_name' 		=> $Parame['document_name'],
				'certificates_number' 	=> $Parame['certificates_number'],
				'ID_front_img' 			=> $Parame['ID_front_img'],
				'ID_back_img' 			=> $Parame['ID_back_img'],
				'license_img' 			=> $Parame['license_img'],
				'check_status' 			=> 1
		);
		//商户信息
		$shopData = array(
				'category_id'	=> $Parame['category_id'],
				'name'			=> $Parame['name'],
				'mobile'		=> $Parame['mobile'],
				'longitude'		=> $Parame['longitude'],
				'latitude'		=> $Parame['latitude'],
				'proportion'	=> $Parame['proportion'],
				'province'		=> $Parame['province'],
				'area'			=> $Parame['area'],
				'county'		=> $Parame['county'],
				'address'		=> $Parame['address'],
				'create_time'	=> NOW_TIME,
				'check_status' 	=> 0
		);
		
		$user = M('user')->where(array('phone'=>$phone,'type'=>2))->find();
		if($user['id']){
			if($user['check_status'] == 3){
				$res 		= M('user')->where(array('id'=>$user['id']))->save($userData);
				$shopRes 	= M('shop')->where(array('uid'=>$user['id']))->save($shopData);
				$memberRes 	= M('ucenter_member')->where(array('username'=>$user['phone']))->save(array('password'=>md5(sha1($Parame['password']) . '&17@:iY$0?(twB]kru)46J^!9l;.,Z5oE[bI_QmA')));
			}elseif($user['check_status'] == 2){
				return array('Code' =>'100021','Msg'=>$this->Lang['100021']);
			}elseif($user['check_status'] == 1){
				return array('Code' =>'100020','Msg'=>$this->Lang['100020']);
			}
		}else{
			$res = M('user')->add($userData);
			if($res > 0){
				//开始注册
				$_user 				= new UserApi;
				$uid 				= $_user->register($phone, $Parame['password']);	//返回ucentermember数据表用户主键id
				//注册成功
				if($uid > 0){
					//自动分配到对应组里面
					$accessinfo		= M('auth_group_access')->where(array('uid'=>$uid,'group_id'=>2))->find();
					if (empty($accessinfo)){
						M('auth_group_access')->add(array('uid'=>$uid,'group_id'=>2));
					}
					//记录日志
					//addUserLog('新会员注册', $uid);
					//调用登陆
					$ucuserinfo					= $_user->info($uid);
					D("member")->login($ucuserinfo['id']);
				}
				
				$shopData['uid'] = $res;
				$shopRes = M('shop')->add($shopData);
			}
		}
		if($res != false && $shopRes != false ){
			return array('Code' =>'0','Msg'=>$this->Lang['100016']);
		}
		return array('Code' =>'100017','Msg'=>$this->Lang['100017']);
	}
	
	/**
	 * 申请入住信息
	 */
	private function shopInfo($Parame){
		//门店分类
		$category_list				= array();
		$category_list 				= M('scategory')->field('id,name,pid')->where(array('status'=>1))->order('sort desc,id asc')->select();
		$category_list 				= list_to_tree($category_list, $pk = 'id', $pid = 'pid', $child = 'list', $root = 0);
		$shop 						= M('shop')->where(array('uid'=>$Parame['uid']))->find();
		$shopPic 					= M('shop_pic')->where(array('shop_id'=>$shop['id']))->order('id asc')->select();
		if(empty($shopPic)){
			$shopPic = array();
		}
		$categoryName				= M('scategory')->where(array('id'=>$shop['category_id']))->getField('name');
		$provinceName				= M('area')->where(array('id'=>$shop['province']))->getField('area');
		$areaName					= M('area')->where(array('id'=>$shop['area']))->getField('area');
		$countyName					= M('area')->where(array('id'=>$shop['county']))->getField('area');
		
		//店家照片张数
		$shop_pic_num = M('business_shop_pic_order')->where(array('b_uid'=>$Parame['uid'],'status'=>2))->sum('pic_num');
		
		$data = array(
				'shop_name'			=> !empty($shop['shop_name']) ? $shop['shop_name'] : '',
				'category_id'		=> !empty($shop['category_id']) ? $shop['category_id'] : '',
				'category_name'		=> !empty($categoryName) ? $categoryName : '',
				//'category_list'		=> $category_list,
				'longitude'			=> !empty($shop['longitude']) ? $shop['longitude'] : '',
				'latitude'			=> !empty($shop['latitude']) ? $shop['latitude'] : '',
				'province'			=> !empty($shop['province']) ? $shop['province'] : '',
				'province_name'		=> !empty($provinceName) ? $provinceName : '',
				'area'				=> !empty($shop['area']) ? $shop['area'] : '',
				'area_name'			=> !empty($areaName) ? $areaName : '',
				'county'			=> !empty($shop['county']) ? $shop['county'] : '',
				'county_name'		=> !empty($countyName) ? $countyName : '',
				'address'			=> !empty($shop['address']) ? $shop['address'] : '',
				'desc'				=> !empty($shop['desc']) ? $shop['desc'] : '',
				'face'				=> !empty($shop['face']) ? $shop['face'] : '',
				'shop_pic_num'		=> 4+intval($shop_pic_num),
				'shop_pic'			=> $shopPic,
				//'receipt_code'		=> !empty($shop['receipt_code']) ? $shop['receipt_code'] : '',
		);
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 申请入住信息修改
	 */
	private function shopInfoSubmit($Parame){
		$data 					= array();
		$data['shop_name'] 		= $Parame['shop_name'];
		$data['category_id'] 	= $Parame['category_id'];
		$data['longitude'] 		= $Parame['longitude'];
		$data['latitude'] 		= $Parame['latitude'];
		$data['province'] 		= $Parame['province'];
		$data['area'] 			= $Parame['area'];
		$data['county'] 		= $Parame['county'];
		$data['address'] 		= $Parame['address'];
		$data['desc'] 			= $Parame['desc'];
		$data['face'] 			= $Parame['face'];
		//$data['receipt_code'] 	= $Parame['receipt_code'];
		$data['update_time'] 	= NOW_TIME;
		$data['check_status'] 	= 1;
		
		//商家是否存在
		$shop = M('shop')->field('id')->where(array('uid'=>$Parame['uid']))->find();
		if($shop['id']){
			$res = M('shop')->where(array('id'=>$shop['id']))->save($data);
			if($res !== false){
				if(!empty($Parame['shop_pic'])){
					$shopId = M();
					M('shop_pic')->where(array('shop_id'=>$shop['id']))->delete();
					$shopPic = json_decode($Parame['shop_pic'],true);
					foreach ($shopPic as $key=>$value){
						M('shop_pic')->add(array('shop_id'=>$shop['id'], 'shop_pic'=>$value['shop_pic'], 'shop_description'=>$value['shop_description']));
					}
				}
				return array('Code' =>'0','Msg'=>$this->Lang['100016']);
			}
		}
		return array('Code' =>'100017','Msg'=>$this->Lang['100017']);
	}
	
	/**
	 * 财务信息
	 */
	private function financeDetail($Parame){
		$user 	= M('user')->where(array('id'=>$Parame['uid']))->find();
		$data 	= array();
		if(!empty($user)){
			$data['xindou'] 		= $user['current_xinlidou']+$user['current_xianglidou'];
			$data['xindou'] 		= number_format($data['xindou'],3,".","");
			//获取商家比例
			$grade = M('shop')->where(array('uid'=>$Parame['uid']))->getField('grade');
			$appreciation 			= M('shop_proportion')->where(array('grade'=>$grade))->getField('appreciation');
			$data['money'] 			= ($user['current_xinlidou']+$user['current_xianglidou'])*$appreciation;
			$data['money'] 			= number_format($data['money'],2,".","");
		}
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 财务信息列表
	 */
	private function financeList($Parame){
		
		$uid 					= intval($Parame['uid']);
		//获取店铺id
		$shop_id				= M('shop')->where(array('uid'=>$uid))->getField('id');
		
		$where 					= array();
		$where['shop_id']		= $shop_id;
		$where['pay_status']	= 1;
		//总数
		$count      			= M('business_order')->where($where)->count();
		$Page       			= new \Think\Page($count,10);
		$business_order 		= M('business_order')->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
	
		//增值比
		$grade 					= M('shop')->where(array('id'=>$shop_id))->getField('grade');
		$appreciation 			= M('shop_proportion')->where(array('grade'=>$grade))->getField('appreciation');
		
		$data = $row = array();
		if(!empty($business_order)){
			foreach ($business_order as $key=>$value){
				$row[$key]['xindou'] 				= $value['xinlidou']+$value['xianglidou'];
				$row[$key]['money'] 				= $value['pay_money'];
				$row[$key]['total_money'] 			= $value['total_money'];
				$row[$key]['withdraw_money'] 		= ($value['xinlidou']+$value['xianglidou'])*$appreciation;
				$row[$key]['create_time'] 			= date('Y-m-d', $value['pay_time']);
			}
		}
		
		$data['list']			= $row;
		$data['page']			= $Parame['page'];
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 提现
	 */
	private function withdraw($Parame){
		
		$uid 					= $Parame['uid'];
		$card_id 				= $Parame['card_id'];
		$xindou 				= $Parame['money'];	//这个提现的是鑫豆
		
		//提现鑫豆不能小于0
		if($xindou <= 0){
			return array('Code' =>'1','Msg'=>'提现的鑫豆必须大于0');
		}
		
		//用户当前鑫豆 鑫豆必须全部提现
		$user 					= M('user')->field('current_xinlidou,current_xianglidou')->where(array('id'=>$uid))->find();
		$current_xindou			= $user['current_xinlidou']+$user['current_xianglidou'];
		if($xindou != $current_xindou){
			return array('Code' =>'1','Msg'=>'鑫豆必须全部提现');
		}
		
		//增值比自动计算
		$grade 					= M('shop')->where(array('uid'=>$uid))->getField('grade');
		$proportion 			= M('shop_proportion')->where(array('grade'=>$grade))->getField('ratio');
	
		//银行卡是否绑定
		$count = M('bank_card')->where(array('id'=>$card_id))->count();
		if($count>0){
			M()->startTrans();
			$data 					= array();
			$data['uid'] 			= $uid;
			$data['card_id'] 		= $card_id;
			$data['xinlidou'] 		= $user['current_xinlidou'];
			$data['xianglidou'] 	= $user['current_xianglidou'];
			$data['xindou'] 		= $xindou;
			$data['appreciation'] 	= $proportion;
			$data['money'] 			= $xindou*$proportion;
			$data['status'] 		= 0;
			$data['pay_status'] 	= 0;
			$data['create_time'] 	= NOW_TIME;
			$res = M('withdraw')->add($data);
			if($res){
				//用户当前鑫豆减去
				$userRes = M('user')->where(array('id'=>$uid))->save(array('current_xinlidou'=>0, 'current_xianglidou'=>0));
				//交易日志
				/* $logRes = M('transaction_log')->add(array(
						'uid'			=> $uid,
						'description'	=> '提现',
						'type'			=> 1,
						'symbol'		=> '-',
						'money'			=> $money,
						'create_time'	=> NOW_TIME
				));
				if($memberRes && $logRes){
					M()->commit();
					return array('Code' =>'0','Msg'=>$this->Lang['100016']);
				} */
				
				if($userRes){
					M()->commit();
					return array('Code' =>'0','Msg'=>$this->Lang['100016']);
				}
				M()->rollback();
				return array('Code' =>'1','Msg'=>$this->Lang['100017']);
			}else{
				M()->rollback();
				return array('Code' =>'1','Msg'=>$this->Lang['100017']);
			}
		}else{
			return array('Code' =>'1','Msg'=>$this->Lang['101309']);
		}
		return array('Code' =>'1','Msg'=>$this->Lang['100017']);
	}
	
	/**
	 * 我的店家
	 */
	private function store($Parame){
		$data 					= array();
		$shopPrice 				= M('shop_price')->select();
		$data['shop_price'] 	= $shopPrice;
		$user 					= M('user')->where(array('id'=>$Parame['uid']))->find();
		$data['xinlidou']		= $user['current_xinlidou'];
		$data['xianglidou']		= $user['current_xianglidou'];
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 我的店家提交
	 */
	private function storeSubmit($Parame){
		//价格表id
		$id 		= intval($Parame['id']);
		//用户id
		$uid 		= intval($Parame['uid']);
		$user 		= M('user')->field('current_xinlidou,current_xianglidou')->where(array('id'=>$uid))->find();
		//商家id
		$shop_id 	= M('shop')->where(array('uid'=>$uid))->getField('id');
		
		//鑫利豆
		$xinlidou 	= $Parame['xinlidou'];
		if($xinlidou != ''){
			if(!judge_decimal($xinlidou, 0)){
				return array('Code' =>'1','Msg'=>'鑫利豆格式错误,必须为整数');
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
				return array('Code' =>'1','Msg'=>'享利豆格式错误,必须为整数');
			}
			if($xianglidou > $user['current_xianglidou']){
				return array('Code' =>'1','Msg'=>'享利豆不能大于你现有的享利豆');
			}
		}else{
			$xianglidou = 0;
		}
		
		$shop_price = M('shop_price')->where(array('id'=>$id))->find();
		$totalMoney = $shop_price['price'];
		$picNum		= $shop_price['num'];
		if(($xinlidou+$xianglidou) > $totalMoney){
			return array('Code' =>'1','Msg'=>'你使用的豆子不能超过订单的总金额');
		}
		//数据
		$data 							= array();
		$data['b_uid'] 					= $uid;
		$data['shop_id'] 				= $shop_id;
		$data['order_no'] 				= create_orderid();
		$data['out_trade_no'] 			= 'REWORD'.date('YmdHis',NOW_TIME).randomString('6',0);
		$data['xinlidou'] 				= $xinlidou;
		$data['xianglidou'] 			= $xianglidou;
		$data['total_money'] 			= $totalMoney;
		$data['pay_money'] 				= $totalMoney-$xinlidou-$xianglidou;
		$data['pic_num'] 				= $picNum;
		/* if($data['pay_money'] == 0){
			$data['pay_status'] 			= 1;
			$data['pay_time'] 				= NOW_TIME;
			$data['status'] 				= 2;
		}else{
			$data['pay_status'] 			= 0;
			$data['status'] 				= 1;
		} */
		$data['status'] 				= 1;
		$data['create_time'] 			= NOW_TIME;
		
		$res = M('business_shop_pic_order')->where(array('id'=>$id))->add($data);
		if($res){
			return array('Code' =>'0','Msg'=>$this->Lang['100016']);
		}else{
			return array('Code' =>'100017','Msg'=>$this->Lang['100017']);
		}
	}

	/**
	 * 线下订单提交
	 */
	private function orderSubmit($Parame){
		//用户id
		$uid 			= intval($Parame['uid']);
		//商家id
		$shop_id 		= intval($Parame['shop_id']);
		//金额
		$money			= trim($Parame['money']);
		
		$user 			= M('user')->field('current_xinlidou,current_xianglidou')->where(array('id'=>$uid))->find();
		//鑫利豆
		$xinlidou 		= $Parame['xinlidou'];
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
		$xianglidou 	= $Parame['xianglidou'];
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
		
		if(($xinlidou+$xianglidou) > $money){
			return array('Code' =>'1','Msg'=>'你使用的豆子不能超过订单的总金额');
		}
		$pay_money 				= $money-($xinlidou+$xianglidou);
		
		$data  					= array();
		$data['uid']			= $uid;
		$data['shop_id']		= $shop_id;
		$data['order_no']		= create_orderid();
		$data['xinlidou']		= $xinlidou;
		$data['xianglidou']		= $xianglidou;
		$data['total_money']	= $money;
		$data['pay_money']		= $pay_money;
		$data['create_time']	= NOW_TIME;
		M('business_order')->add($data);
		
		
		
		/***************************产生红包*********************************/
		if($pay_money > 0){
			$this->setRedPacket($uid, $shop_id, $pay_money);
		}
		/***************************产生红包*********************************/
		
		/***************************产生分销*********************************/
		if($pay_money > 0){
			$this->setDistribution($uid, $shop_id, $pay_money);
		}
		/***************************产生分销*********************************/
		
		/***************************产生消费分销*********************************/
		if($pay_money > 0){
			//$this->setDistribution($uid, $shop_id, $pay_money);
		}
		/***************************产生消费分销*********************************/
		
		/***************************支付成功*********************************/
		if($pay_money > 0){
			//根据店铺id获取商家用户id
			$buid = M('shop')->where(array('id'=>$shop_id))->getField('uid');
			//商家增加钱和鑫豆
			$row 						= array();
			$row['total_money'] 		= array('exp',"total_money+$pay_money");
			$row['current_money'] 		= array('exp',"current_money+$pay_money");
			$row['total_xinlidou'] 		= array('exp',"total_xinlidou+$xinlidou");
			$row['current_xinlidou'] 	= array('exp',"current_xinlidou+$xinlidou");
			$row['total_xianglidou'] 	= array('exp',"total_xianglidou+$xianglidou");
			$row['current_xianglidou'] 	= array('exp',"current_xianglidou+$xianglidou");
			M('user')->where(array('id'=>$buid))->save($row);
		}
		/***************************支付成功*********************************/
		
		
		
		
		
		
		
		
		
		
		
		
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>array('oid'=>$data['order_no'], 'money'=>$data['pay_money'], 'table'=>'business'));
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
		foreach ($proportionArr as $key=>$value){
			$row['uid'] 			= $uid;
			$row['shop_id'] 		= $shop_id;
			$row['type'] 			= $value['type'];
			$row['money'] 			= $money;
			$row['proportion'] 		= $value['proportion'];
			$row['num'] 			= $money*$value['proportion'];
			if($key == 1){
				if(date('g', NOW_TIME) < 11){
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
				if(date('g', NOW_TIME) < 11){
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
			
			//$row['packet_num'] 		= 1;
			
			$row['create_time'] 	= NOW_TIME;
			//插入红包表
			M('red_packet')->add($row);
		}
	}
	
	/**
	 * 生成分销
	 */
	private function setDistribution($uid, $money){
		//分销产生两级 产生鑫利豆
		$frist_id = M('user')->where(array('id'=>$uid))->getField('pid');
		if($frist_id > 0){
			//处理分销
			
			//二级分销
			$second_id = M('user')->where(array('id'=>$frist_id))->getField('pid');
			if($second_id > 0){
				//处理分销
			}
		}
		
		
	}
	
	
	
	
	/**
	 * 商家店铺详情
	 */
	private function shopDetail($Parame){
		$shop = M('shop')->field('id,shop_name,sales_num,score,address,mobile,face,pics,longitude,latitude')->where(array('id'=>$Parame['shop_id'], 'status'=>1))->find();
		$data = array();
		if(!empty($shop)){
			$data['id'] 			= $shop['id'];
			$pics 					= explode(',', $shop['pics']);
			$data['pics'] 			= $pics[0];
			$data['face'] 			= $shop['face'];
			$data['shop_name'] 		= $shop['shop_name'];
			$data['sales_num'] 		= $shop['sales_num'];
			$data['score'] 			= $shop['score'];
			$data['mobile'] 		= $shop['mobile'];
			$data['address'] 		= $shop['address'];
			$data['longitude'] 		= $shop['longitude'];
			$data['latitude'] 		= $shop['latitude'];
			$discount				= M('platform_config')->where(array('name'=>'DISCOUNT'))->getField('value');
			$data['discount']		= empty($discount) ? 10 : floatval($discount);
			$data['category_list'] 	= M('category')->field('id,name')->where(array('shop_id'=>$Parame['shop_id'], 'status'=>1))->select();
		}
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	
	
	
	/**
	 * 分享接口
	 */
	private function share($Parame){
		$shop = M('shop')->where(array('id'=>$Parame['shop_id'], 'status'=>1))->find();
		$data = array();
		if(!empty($shop)){
			//分享链接
			$data['url'] 				= 'http://'.WEB_DOMAIN.'/Home/Index/share/shop_id/'.$Parame['shop_id'].'/pid/'.$Parame['uid'].'.html';
			//分享图标
			$data['img'] 				= $shop['share_icon'];
			//分享图片
			$data['big_img'] 			= $shop['share_pic'];
			//分享标题
			$data['title'] 				= $shop['shop_name'];
			//分享内容
			$data['content'] 			= M('platform_config')->where(array('name'=>'SHARE_CONTENT'))->getField('value');
		}
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
}
?>