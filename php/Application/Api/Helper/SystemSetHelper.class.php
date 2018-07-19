<?php
namespace Api\Helper;
/**
 * 系统设置
 * @author 王远庆
 */
class SystemSetHelper extends BaseHelper{
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
	 * 获得开播提醒设置-全局
	 * @author 王远庆
	 */
	private function getIsCallSet($Parame){
		$is_call							= M('direct_seeding_room')->where(array('uid'=>$Parame['uid']))->getField('is_call');
		return intval($is_call) ;
	}

	/**
	 * 获得未关注私信设置
	 * @author 王远庆
	 */
	private function getNoFollowSend($Parame){
		$no_follow_send					= M('direct_seeding_room')->where(array('uid'=>$Parame['uid']))->getField('no_follow_send');
		return intval($no_follow_send);
	}

	/**
	 * 消息设置
	 * @author 王远庆
	 */
	private function noticeSet($Parame){
		//用户ID检验
		$ucheck = $this->check_user($Parame['uid'], $Parame['hashid']);
		if ($ucheck['Code'] > 0){
			return $ucheck;
		}
		switch ($Parame['setType']){
			case 1://未关注私信设置
				//检查是否有直播间
				$model		= M('direct_seeding_room');
				$info		= $model->where(array('uid'=>$Parame['uid']))->field('id,no_follow_send')->find();
				if (empty($info) || $info['id'] <= 0){
					return array('Code' =>'100201','Msg'=>$this->Lang['100201']);
				}
				$no_follow_send	= $info['no_follow_send'] == 1 ? 0 : 1;
				$model->where(array('id'=>$info['id']))->setField('no_follow_send',$no_follow_send);
				return array('Code' =>'0','Msg'=>$this->Lang['100803']);
				break;
			case 2://开播提醒-全局
				//检查是否有直播间
				$model		= M('direct_seeding_room');
				$info		= $model->where(array('uid'=>$Parame['uid']))->field('id,is_call')->find();
				if (empty($info) || $info['id'] <= 0){
					return array('Code' =>'100201','Msg'=>$this->Lang['100201']);
				}
				$is_call	= $info['is_call'] == 1 ? 0 : 1;
				$model->where(array('id'=>$info['id']))->setField('is_call',$is_call);
				return array('Code' =>'0','Msg'=>$this->Lang['100803']);
				break;
			case 3://开播提醒-个人
				if ($Parame['fid'] <= 0){
					return array('Code' =>'100804','Msg'=>$this->Lang['100804']);
				}
				//检测是否有关注过
				$model			= M('user_follow');
				$info			= $model->where(array('uid'=>$Parame['fid'],'fid'=>$Parame['uid']))->field('id,is_call')->find();
				if (!empty($info) && $info['id'] > 0){
					$is_call	= $info['is_call'] == 1 ? 0 : 1;
					$model->where(array('id'=>$info['id']))->setField('is_call',$is_call);
					return array('Code' =>'0','Msg'=>$this->Lang['100803']);
				}else{
					return array('Code' =>'1000595','Msg'=>$this->Lang['1000595']);
				}
				break;
			default:
				return array('Code' =>'100802','Msg'=>$this->Lang['100802']);
				break;
		}
	}
}
?>