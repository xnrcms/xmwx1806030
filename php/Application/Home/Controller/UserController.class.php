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
class UserController extends Controller {
	
	/**
	 * 注册
	 */
	public function login(){
		if(IS_POST){
			$mobile				= I('post.mobile');
			$checkcode			= I('post.checkcode');
			//首先验证手机号是否合法
			if (Mobile_check($mobile,array(1,2,3,4)) == false){
				$this->ajaxReturn(array('Code' =>'1','Msg'=>'手机号格式不正确'));
			}
			//验证验证码是否正确//测试账号，不需要验证码
			$ischeck			= R('Api/Sms/checkcode', array('mobile'=>$mobile,'code'=>$checkcode,'type'=>3));
			if ($ischeck <= 0){
				$this->ajaxReturn(array('Code' =>'1','Msg'=>'手机验证码错误'));
			}
			//验证手机号是否注册
			$count = M('user')->where(array('phone'=>$mobile,'type'=>1))->count('id');
			if($count <= 0){
				$this->ajaxReturn(array('Code' =>'1','Msg'=>'手机号未注册'));
			}
			
			$user = M('user')->where(array('phone'=>$mobile))->find();
			//删除验证码
			R('Api/Sms/delcode', array('mobile'=>$mobile,'code'=>$checkcode,'type'=>3));
			
			/* 环信 */
			if (empty($user['hx_username'])) {
				/* 环信注册 */
				vendor('Hx.Hx');
				$hx       				= new \vendor\Hx\Hxcall();
				$user['hx_username'] 	= md5(C('DATA_AUTH_KEY') . $user['phone']);         //加密账号信息，避免暴露
				$user['hx_password'] 	= md5(C('DATA_AUTH_KEY') . $user['password']);  	//加密密码信息
				$hx_res   				= $hx->hx_register($user['hx_username'], $user['hx_password']);
				M('user')->where(array('id' => $user['id']))->save(array('hx_username' => $user['hx_username'], 'hx_password' => $user['hx_password']));
			}
			session('uid',$user['id']);
			cookie(md5('home_username'.C('DATA_AUTH_KEY')),FauthCode($user['phone'],'ENCODE'),2592000); // 指定cookie保存30天时间
			$this->ajaxReturn(array('Code' =>'0','Msg'=>'登录成功'));
		}else{
			$this->display();
		}
	}
	
	/**
	 * 登录
	 */
	public function register(){
		if(IS_POST){
			$mobile				= I('post.mobile');
			$checkcode			= I('post.checkcode');
			$code				= I('post.code');
			$longitude			= I('post.longitude');
			$latitude			= I('post.latitude');
			
			//首先验证手机号是否合法
			if (Mobile_check($mobile,array(1,2,3,4)) == false){
				$this->ajaxReturn(array('Code' =>'1','Msg'=>'手机号格式不正确'));
			}
			//验证验证码是否正确//测试账号，不需要验证码
			$ischeck			= R('Api/Sms/checkcode', array('mobile'=>$mobile,'code'=>$checkcode,'type'=>1));
			if ($ischeck <= 0){
				$this->ajaxReturn(array('Code' =>'1','Msg'=>'手机验证码错误'));
			}
			//验证手机号是否注册
			$count = M('user')->where(array('phone'=>$mobile,'type'=>1))->count('id');
			if($count>0){
				$this->ajaxReturn(array('Code' =>'1','Msg'=>'手机号已注册'));
			}
			//未注册,开始注册
			$password			= md5($mobile);
			$nickname			= '省鑫用户';
			$platform_code 		= M('platform_config')->where(array('name'=>'CODE'))->getField('value');
			if($code == $platform_code){	//平台邀请码
				$pid			= 0;
			}else{
				$pid 			= M('user')->where(array('code'=>strtoupper($code)))->getField('id');
				if($pid < 1){
					$this->ajaxReturn(array('Code' =>'1','Msg'=>'邀请码不存在,请填写正确的邀请码'));
				}
			}
			
			//我的邀请码
			$my_code 			= randomCode();
			$uid 				= M('user')->add(array(
					'type' => 1,
					'code' => $my_code,
					'phone' => $mobile,
					'password' => $password,
					'pid' => $pid,
					'nickname' => $nickname,
					'jpushid' => '',
					'create_time' => NOW_TIME,
					'status' => 1,
					'check_status' => 2,
					'longitude' => $longitude,
					'latitude' => $latitude
			));
			//注册成功
			if($uid > 0){
				//记录日志
				//addUserLog('新会员注册', $uid);
				//调用登陆
				$user	= M('user')->where(array('id'=>$uid))->find();
				if(!empty($user) && $user['id'] > 0){
					//删除验证码
					R('Api/Sms/delcode', array('mobile'=>$mobile,'code'=>$checkcode,'type'=>1));
					/* 环信 */
					if (empty($user['hx_username'])) {
						/* 环信注册 */
						vendor('Hx.Hx');
						$hx       				= new \vendor\Hx\Hxcall();
						$user['hx_username'] 	= md5(C('DATA_AUTH_KEY') . $user['phone']);         //加密账号信息，避免暴露
						$user['hx_password'] 	= md5(C('DATA_AUTH_KEY') . $user['password']);  	//加密密码信息
						$hx_res   				= $hx->hx_register($user['hx_username'], $user['hx_password']);
						M('user')->where(array('id' => $user['id']))->save(array('hx_username' => $user['hx_username'], 'hx_password' => $user['hx_password']));
					}
					/*登录成功后*/
					session('uid',$user['id']);
					cookie(md5('home_username'.C('DATA_AUTH_KEY')),FauthCode($user['phone'],'ENCODE'),2592000); // 指定cookie保存30天时间
					$this->ajaxReturn(array('Code' =>'0','Msg'=>'注册成功'));
				} else {
					$this->ajaxReturn(array('Code' =>'1','Msg'=>'未知错误'));
				}
			}
		}else{
			$this->display();
		}
	}
	
	/**
	 * 发送短信
	 */
	public function sendCode(){
		//手机号码
		$mobile = I('post.mobile', '', 'trim');
		$type = I('post.type', 0, 'intval');
		if($mobile == ''){
			$this->ajaxReturn(array('Code' =>'1','Msg'=>'请输入手机号码'));
		}
		//手机号校验
		$mobile		= safe_replace($mobile);
		if (Mobile_check($mobile,array(1)) == false){
			$this->ajaxReturn(array('Code' =>'1','Msg'=>'手机号码格式不正确'));
		}
		//删除所有过期验证码
		M('Sms')->where(array('validity'=>array('lt',NOW_TIME)))->delete();
		//根据短信类型校验是否发送短信
		switch ($type) {
			case 1://注册短信 需要验证手机号是否已经被注册
			case 5://短信绑定
				$count = M('user')->where(array('phone'=>$mobile,'type'=>1))->count();
				if($count > 0){
					$this->ajaxReturn(array('Code' => 1, 'Msg' => '该手机已经被注册'));
				}
				break;
			case 2:	//密码找回 需要验证手机手否已经注册
			case 3:	//手机短信登录
			case 4:	//解除绑定
				$count = M('user')->where(array('phone'=>$mobile,'type'=>1))->count();
				if($count < 1){
					$this->ajaxReturn(array('Code' => 1, 'Msg' => '该手机未注册'));
				}
				//手机短信验证登录 无需验证手机号是否注册
				break;
			case 6:	//绑定第三方
				break;
			default:
				$this->ajaxReturn(array('Code' => 1, 'Msg' => '短信发送类型错误[1手机短信注册 2找回密码 3手机短信登录 4解除手机绑定 5手机绑定 6绑定第三方]'));
				break;
		}
     	
     	$SmsDb = D('Sms');
     	$validity = 60 * 30; //30分钟
     	$limitime = 60 * 2; //120秒
     	$limitip = 5; //同一个IP最多可以发送5次
     	//频率限制，120秒内不允许发送下一条
     	$ctime = $SmsDb->where(array('mobile' => $mobile, 'status' => 1))->order('id desc')->limit(1)->getField('create_time');
     	if (!empty($ctime) && ($ctime + $limitime) > NOW_TIME) {
     		$this->ajaxReturn(array('Code' => '100104', 'Msg' => '短息发送频率太快'));
     	}
     	//IP限制，同一个手机号，同个IP每天只能发送5次
     	$ipcont = $SmsDb->where(array('mobile' => $mobile, 'ip' => getip(), 'create_time' => array('elt', strtotime(date('Y-m-d', strtotime('+1 day'))))))->count('ip');
     	if ($ipcont >= $limitip) {
     		$this->ajaxReturn(array('Code' => '100105', 'Msg' => '短信发送次数已达上限'));
     	}
     	//生成短信内容
     	$sign = '【省鑫】';
     	$code = randomString('6', 0);
     	$conArr[1] = '用户注册验证码为：' . $code . $sign; //可扩展多个验证码内容格式
     	$conArr[2] = '找回密码验证码为：' . $code . $sign;
     	$conArr[3] = '登录账号验证码为：' . $code . $sign;
     	$conArr[4] = '解除账号绑定验证码为：' . $code . $sign;
     	$conArr[5] = '手机绑定验证码为：' . $code . $sign;
     	$conArr[6] = '账号绑定第三方验证码为：' . $code . $sign;
     	$content = $conArr[$type];
     	if (empty($content)) {
     		$this->ajaxReturn(array('Code' => '100106', 'Msg' => '短信内容不能为空'));
     	}
     	//短信发送
     	$ret = sendcode($mobile, $code);
     	$ret = 1;
     	if ($ret) {
     		$smsdata['mobile'] = $mobile;
     		$smsdata['checkcode'] = $code;
     		$smsdata['sendtype'] = $type;
     		$smsdata['content'] = $content;
     		$smsdata['validity'] = NOW_TIME + $validity;
     		D('Sms')->update($smsdata);
     		$this->ajaxReturn(array('Code' => '0', 'Msg' => '发送成功'));
     	} else {
     		$this->ajaxReturn(array('Code' => '100107', 'Msg' => '短信发送失败'));
     	}
	}
	
}