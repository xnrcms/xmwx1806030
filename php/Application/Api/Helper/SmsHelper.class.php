<?php
namespace Api\Helper;
use User\Api\UserApi;
/**
 * 短信发送
 * @author 王远庆
 */
class SmsHelper extends BaseHelper{
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
	private function setSmsContent(){
		//生成短信内容
		$sign			= '【迷路客直播】';
		$this->code 	= randomString('6',0);
		$conArr[1]		= '用户注册验证码为：'.$this->code .$sign;//可扩展多个验证码内容格式
		$conArr[2]		= '找回密码验证码为：'.$this->code .$sign;
		$conArr[3]		= '您的验证码为：'.$this->code .$sign;
		$conArr[4]		= '您的验证码为：'.$this->code .$sign;
		return $conArr;
	}

	/**
	 * Brand列表
	 * @author 王远庆
	 */
	private function sendCode($Parame){
		//删除所有过期验证码
		M('Sms')->where(array('validity'=>array('lt',NOW_TIME)))->delete();

		//手机号校验
		$mobile		= safe_replace($Parame['mobile']);
		if (Mobile_check($mobile,array(1)) == false){
			return array('Code' =>'100035','Msg'=>$this->Lang['100035']);
		}
		//根据短信类型校验是否发送短信
		$error	= '';
		switch ($Parame['checktype']){
			case 1:
				//注册 需要验证手机号是否已经被注册
				$user 		= new UserApi;
				$res 		= $user->checkMobile($mobile,3);
				if($res != 1){
					return array('Code' =>(($res * (-1)-1)+100100),'Msg'=>$user->showRegError($res));
				}
				break;
			case 2:
				//密码找回 需要验证手机手否已经注册
				$user 		= new UserApi;
				$res 		= $user->checkMobile($mobile);
				if ($res != -11){
					if ($res == 1){
						return array('Code' =>'100103','Msg'=>$this->Lang['100103']);
					}else{
						return array('Code' =>(($res * (-1)-1)+100100),'Msg'=>$user->showRegError($res));
					}
				}
			case 3:
				//手机短信验证登录 无需验证手机号是否注册
				break;
			case 4:
				//绑定手机 需要验证手机号是否已经被注册
				$user 		= new UserApi;
				$res 		= $user->checkMobile($mobile,3);
				if($res != 1){
					return array('Code' =>(($res * (-1)-1)+100100),'Msg'=>$user->showRegError($res));
				}
				break;
			default:
				return array('Code' =>'100102','Msg'=>$this->Lang['100102']);
				break;
		}
		$SmsDb	= D('Sms');
		$validity	= 60*30;//30分钟
		$limitime	= 60;//60秒
		$limitip	= 10;//同一个IP最多可以发送5次
		//频率限制，120秒内不允许发送下一条
		$ctime		= $SmsDb->where(array('mobile'=>$mobile,'status'=>1))->order('id desc')->limit(1)->getField('create_time');
		if (!empty($ctime) && ($ctime+$limitime) > NOW_TIME){
			return array('Code' =>'100104','Msg'=>$this->Lang['100104']);
		}
		//IP限制，同一个手机号，同个IP每天只能发送5次
		$ipcont			= $SmsDb->where(array('mobile'=>$mobile,'ip'=>getip(),'create_time'=>array('elt',strtotime(date('Y-m-d',strtotime('+1 day'))))))->count('ip');
		if ($ipcont >= $limitip){
			return array('Code' =>'100105','Msg'=>$this->Lang['100105']);
		}
		
		$conArr		= $this->setSmsContent();
		$content	= $conArr[$Parame['checktype']];
		if (empty($content)){
			return array('Code' =>'100106','Msg'=>$this->Lang['100106']);
		}
		//短信发送
		//$ret 		= sendcode($mobile,$content);
		$ret 		= 1;
		if($ret){
			$smsdata['mobile']		= $mobile;
			$smsdata['checkcode']	= $this->code;
			$smsdata['sendtype']	= $Parame['checktype'];
			$smsdata['content']		= $content;
			$smsdata['validity']	= NOW_TIME+$validity;
			D('Sms')->update($smsdata);
			return array('Code' =>'0','Msg'=>$this->Lang['100110']);
		}else{
			return array('Code' =>'100107','Msg'=>$this->Lang['100107']);
		}
	}
	
	
	
}
?>