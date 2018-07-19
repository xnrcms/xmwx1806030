<?php
namespace Api\Helper;
/**
 * 佣金
 */
class CommissionHelper extends BaseHelper{
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
	 * 佣金排行榜
	 */
	private function top($Parame){
		//处理电话空格
		$mobile = preg_replace('/\s+/', '', $Parame['mobile']);
		$mobileArr = explode(',', $mobile);
		//用户扩展表信息
		$member = M('member')->alias('m')->join('__AUTH_GROUP_ACCESS__ g ON m.uid = g.uid','LEFT')->field('m.uid,m.nickname,m.face,m.total_commission')->where(array('g.group_id'=>array(array('eq',2),array('eq',3), 'or')))->order('m.total_commission DESC, m.uid ASC')->select();
		//用户主表信息
		$ucenterMember = M('ucenter_member')->field('id,username')->select();
		$ucenterMemberById = array();
		if($ucenterMember){
			foreach ($ucenterMember as $key=>$value){
				$ucenterMemberById[$value['id']] = $value['username'];
			}
		}
		$data = array();
		if($member){
			$i = $j = 1;
			foreach ($member as $key=>$value){
				if(in_array($ucenterMemberById[$value['uid']], $mobileArr) || $value['uid'] == $Parame['uid']){
					//名次
					$data[$j]['rank'] 		= $i++;
					//用户id
					$data[$j]['uid'] 		= $value['uid'];
					//用户头像
					$data[$j]['face'] 		= $value['face'];
					//用户昵称
					$data[$j]['nickname'] 	= $value['nickname'];
					//用户手机号
					$data[$j]['username'] 	= $ucenterMemberById[$value['uid']];
					if($value['uid'] == $Parame['uid']){
						array_unshift($data, $data[$j]);
					}
					$j++;
				}
			}
		}
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 我的助粉
	 */
	private function fans($Parame){
		//用户扩展表信息
		$member = M('member')->field('uid,nickname,face,total_commission')->where(array('pid'=>$Parame['uid']))->order('uid ASC')->select();
		//用户主表信息
		$ucenterMember = M('ucenter_member')->field('id,username')->where(array('pid'=>$Parame['uid']))->select();
		$ucenterMemberById = array();
		if($ucenterMember){
			foreach ($ucenterMember as $key=>$value){
				$ucenterMemberById[$value['id']] = $value['username'];
			}
		}
		$data = array();
		if($member){
			foreach ($member as $key=>$value){
				//用户id
				$data[$key]['uid'] 	= $value['uid'];
				//用户头像
				$data[$key]['face'] 	= $value['face'];
				//用户昵称
				$data[$key]['nickname'] = $value['nickname'];
				//用户手机号
				$data[$key]['username'] = $ucenterMemberById[$value['uid']];
			}
		}
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}

	/**
	 * 分佣明细
	 */
	private function detail($Parame){
		
		$page						= $Parame['page'];
		$limit						= 10;
		//获取数据
		$MainTab					= 'commission_record';
		$MainAlias					= 'main';
		$MainField					= array('id,is_platform,uid,buy_uid,shop_id,money,commission,ratio,create_time');
			
		//主表模型
		$MainModel 					= M($MainTab)->alias($MainAlias);
			
		$RelationTab				= array();
		$RelationTab				= $this->getRelationTab($RelationTab);
		$tables	  					= $RelationTab['tables'];
		$RelationFields				= $RelationTab['fields'];
		$model						= !empty($tables) ? $MainModel->join ( $tables ,'LEFT'): $MainModel;
			
		//检索条件
		$map 						= array();
		$map['main.is_platform']	= 0;
		$map['main.uid']			= $Parame['uid'];
		//排序
		$order						= $MainAlias.'.create_time desc';
		//检索字段
		$fields						= (empty($MainField) ? get_fields_string($MainModel->getDbFields(),$MainAlias).',' : get_fields_string($MainField,$MainAlias).',') . $RelationFields;
		$fields						= trim($fields,',');
			
		//列表数据
		$commissionRecord 			= $this->getLists($model,$map,$order,$fields,$page,$limit,false);
		
		//分佣记录
		//$commissionRecord = M('commission_record')->where(array('uid'=>$Parame['uid'], 'is_platform'=>0))->order('create_time DESC')->select();
		$shopIdArr = array(0);
		if($commissionRecord){
			foreach ($commissionRecord as $key=>$value) {
				$shopIdArr[] = $value['shop_id'];
			}
		}
		//店铺列表
		$shop = M('shop')->where(array('id'=>array('in',$shopIdArr)))->select();
		$shopById = array();
		if($shop){
			foreach ($shop as $key=>$value){
				$shopById[$value['id']] = $value;
			}
		}
		//格式化数据
		$list = array();
		if($commissionRecord){
			foreach ($commissionRecord as $key=>$value) {
				//id
				$list[$key]['id'] 			= $value['id'];
				//时间
				$list[$key]['create_time'] 	= date('Y/m/d H:i:s', $value['create_time']);
				//店铺图片
				$list[$key]['face'] 		= $shopById[$value['shop_id']]['face'];
				//店铺名称
				$list[$key]['shop_name'] 	= $shopById[$value['shop_id']]['shop_name'];
				//店铺评分
				$list[$key]['score'] 		= $shopById[$value['shop_id']]['score'];
				//店铺销量
				$list[$key]['sales_num'] 	= $shopById[$value['shop_id']]['sales_num'];
				//店铺距离
				$distance = getDistanceBetweenPointsNew($Parame['latitude'], $Parame['longitude'], $shopById[$value['shop_id']]['latitude'], $shopById[$value['shop_id']]['longitude']);
				$list[$key]['distance'] 	= $distance['meters']>1000?round($distance['meters']/1000,2).'km':round($distance['meters'],2).'m';
				//抽成
				$list[$key]['commission'] 	= $value['commission'];
			}
		}
		$data['list']			= empty($list) ? array() : $list;
		$data['page']			= $Parame['page'];
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	//分佣详情
	private function commissionDetail($Parame){
		$commissionRecord = M('commission_record')->where(array('id'=>$Parame['id'], 'is_platform'=>0))->find();
		$data = array();
		if(!empty($commissionRecord)){
			//商家名称
			$data['shop_name'] 			= M('shop')->where(array('id'=>$commissionRecord['shop_id']))->getField('shop_name');
			//下线消费者手机号
			$data['username'] 			= M('ucenter_member')->where(array('id'=>$commissionRecord['buy_uid']))->getField('username');
			//消费金额
			$data['money'] 				= $commissionRecord['money'];
			//佣金金额
			$data['commission'] 		= $commissionRecord['commission'];
			//消费时间
			$data['create_time'] 		= date('Y/m/d H:i:s', $commissionRecord['create_time']);
		}
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
}
?>