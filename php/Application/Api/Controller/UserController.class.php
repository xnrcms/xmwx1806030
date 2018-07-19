<?php
namespace Api\Controller;
/**
 * 用户控制器
 */
class UserController extends CommonController {
	public function index(){
		$this->ReturnJson();
	}

	/**
	 * 用户登录
	 */
	public function login(){
		$CheckParam	= array(
			array('time','Int',1,$this->Lang['100001'],'100001'),
			array('hash','String',1,$this->Lang['100002'],'100002'),
			array('username','String',1,$this->Lang['100031'],'100031'),
			array('password','String',1,$this->Lang['100032'],'100032'),
			array('jpushid','String',0),
		);
		$BackData 				= $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']			= 'login';//执行方法名
		//接口调用
		$BackData['isapi']		= true;//是否为内部接口调用
		$parame					= json_encode($BackData);
		$Res					= $this->Helper($parame, 'User');
		$this->ReturnJson($Res);
	}

	/**
	 * 用户注册
	 */
	public function register(){
		$CheckParam	= array(
			array('time','Int',1,$this->Lang['100001'],'100001'),
			array('hash','String',1,$this->Lang['100002'],'100002'),
			array('username','String',1,$this->Lang['100031'],'100031'),
			array('password','String',1,$this->Lang['100032'],'100032'),
			array('repeatpwd','String',1,$this->Lang['100038'],'100038'),
			array('code','String',0),
		);
		$BackData 				= $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['groupid']	= 3;
		$BackData['ac']			= 'register';//执行方法名
		//接口调用
		$BackData['isapi']		= true;//是否为内部接口调用
		$parame					= json_encode($BackData);
		$Res					= $this->Helper($parame, 'User');
		$this->ReturnJson($Res);
	}
	
	/**
	 * 用户手机注册
	 */
	public function mobileRegister(){
		$CheckParam	= array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('username','String',1,$this->Lang['100031'],'100031'),
				array('checkcode','String',1,$this->Lang['100039'],'100039'),
				array('code','String',1,$this->Lang['101825'],'101825'),
		);
		$BackData 				= $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']			= 'mobileRegister';//执行方法名
		//接口调用
		$BackData['isapi']		= true;//是否为内部接口调用
		$parame					= json_encode($BackData);
		$Res					= $this->Helper($parame, 'User');
		$this->ReturnJson($Res);
	}

	/**
	 *获取用户信息
	 */
	public function uInfo(){
		$CheckParam	= array(
		array('time','Int',1,$this->Lang['100001'],'100001'),
		array('hash','String',1,$this->Lang['100002'],'100002'),
		array('uid','Int',1,$this->Lang['100040'],'100040'),
		array('hashid','String',1,$this->Lang['100041'],'100041'),
		);
		$BackData 				= $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']			= 'getUserInfo';//执行方法名
		//接口调用
		$BackData['isapi']		= true;//是否为内部接口调用
		$parame					= json_encode($BackData);
		$Res					= $this->Helper($parame, 'User');
		$this->ReturnJson($Res);
	}

	/**
	 * 更改用户信息
	 */
	public function updateUser(){
		$CheckParam	= array(
		array('time','Int',1,$this->Lang['100001'],'100001'),
		array('hash','String',1,$this->Lang['100002'],'100002'),
		array('uid','Int',1,$this->Lang['100040'],'100040'),
		array('hashid','String',1,$this->Lang['100041'],'100041'),
		array('updata','String',0),
		);
		$BackData 				= $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']			= 'update';//执行方法名
		$BackData['checkGender']= true;
		//接口调用
		$BackData['isapi']		= true;//是否为内部接口调用
		$parame					= json_encode($BackData);
		$Res					= $this->Helper($parame, 'User');
		$this->ReturnJson($Res);
	}

	/**
	 * 用户头像修改
	 */
	public function updateFace(){
		$CheckParam	= array(
		array('time','Int',1,$this->Lang['100001'],'100001'),
		array('hash','String',1,$this->Lang['100002'],'100002'),
		array('uid','Int',1,$this->Lang['100040'],'100040'),
		array('hashid','String',1,$this->Lang['100041'],'100041'),
		array('faceid','Int',1,$this->Lang['100042'],'100042'),
		);
		$BackData 				= $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']			= 'update';//执行方法名
		$BackData['checkGender']= true;
		$BackData['updata']		= json_encode(array('face'=>$BackData['faceid']));
		//接口调用
		$BackData['isapi']		= true;//是否为内部接口调用
		$parame					= json_encode($BackData);
		$Res					= $this->Helper($parame, 'User');
		$this->ReturnJson($Res);
	}
	/**
	 * 手机短信登录后资料补充接口
	 */
	public function perfectUser(){
		$CheckParam	= array(
		array('time','Int',1,$this->Lang['100001'],'100001'),
		array('hash','String',1,$this->Lang['100002'],'100002'),
		array('uid','Int',1,$this->Lang['100040'],'100040'),
		array('hashid','String',1,$this->Lang['100041'],'100041'),
		array('nickname','String',1,$this->Lang['100056'],'100056'),
		array('faceid','Int',1,$this->Lang['100057'],'100057'),
		array('gender','Int',1,$this->Lang['100058'],'100058'),
		);
		$BackData 				= $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']			= 'update';//执行方法名
		$BackData['checkGender']= false;
		$BackData['updata']		= json_encode(array('face'=>$BackData['faceid'],'nickname'=>$BackData['nickname'],'gender'=>$BackData['gender']));
		//接口调用
		$BackData['isapi']		= true;//是否为内部接口调用
		$parame					= json_encode($BackData);
		$Res					= $this->Helper($parame, 'User');
		$this->ReturnJson($Res);
	}

	/**
	 * 修改密码
	 */
	public function updatePwd(){
		$CheckParam	= array(
			array('time','Int',1,$this->Lang['100001'],'100001'),
			array('hash','String',1,$this->Lang['100002'],'100002'),
			array('uid','Int',1,$this->Lang['100040'],'100040'),
			array('hashid','String',1,$this->Lang['100041'],'100041'),
			array('oldpwd','String',1,$this->Lang['100043'],'100043'),
			array('newpwd','String',1,$this->Lang['100044'],'100044'),
			array('repeatpwd','String',1,$this->Lang['100038'],'100038'),
		);
		$BackData 				= $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']			= 'setPwd';//执行方法名
		//接口调用
		$BackData['isapi']		= true;//是否为内部接口调用
		$parame					= json_encode($BackData);
		$Res					= $this->Helper($parame, 'User');
		$this->ReturnJson($Res);
	}

	/**
	 * 找回密码
	 * @author 王远庆
	 */
	public function forgetPwd(){
		$CheckParam	= array(
		array('time','Int',1,$this->Lang['100001'],'100001'),
		array('hash','String',1,$this->Lang['100002'],'100002'),
		array('username','String',1,$this->Lang['100031'],'100031'),
		array('checkcode','String',1,$this->Lang['100039'],'100039'),
		array('newpwd','String',1,$this->Lang['100044'],'100044'),
		//array('repeatpwd','String',1,$this->Lang['100038'],'100038'),
		);
		$BackData 				= $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']			= 'setPwd';//执行方法名
		$BackData['type']		= 2;//找回密码
		//接口调用
		$BackData['isapi']		= true;//是否为内部接口调用
		$parame					= json_encode($BackData);
		$Res					= $this->Helper($parame, 'User');
		$this->ReturnJson($Res);
	}
	
	/**
	 * 第三方登录
	 */
	public function oauthLogin(){
		$CheckParam	= array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('type','Int',1,$this->Lang['1000470'],'1000470'),
				array('openid','String',1,$this->Lang['100048'],'100048'),
				array('nickname','String',0,$this->Lang['100056'],'100056'),
				array('sex','String',0,$this->Lang['100058'],'100058'),
				array('headimgurl','String',0,$this->Lang['100057'],'100057'),
				array('jpushid','String',0),
		);
		$BackData 				= $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']			= 'oauthLogin';//执行方法名
		//接口调用
		$BackData['isapi']		= true;//是否为内部接口调用
		$parame					= json_encode($BackData);
		$Res					= $this->Helper($parame, 'User');
		$this->ReturnJson($Res);
	}
	
	/**
	 * 绑定第三方
	 */
	public function oauthBindOne(){
		$CheckParam	= array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('oauthid','String',1,$this->Lang['101830'],'101830'),
				array('username','String',1,$this->Lang['100031'],'100031'),
				array('checkcode','String',1,$this->Lang['100039'],'100039'),
				//array('jpushid','String',0),
				array('code','String',0,$this->Lang['101825'],'101825'),
		);
		
		$BackData 				= $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']			= 'oauthBindOne';//执行方法名
		$BackData['groupid']	= 3;
		//接口调用
		$BackData['isapi']		= true;//是否为内部接口调用
		$parame					= json_encode($BackData);
		$Res					= $this->Helper($parame, 'User');
		$this->ReturnJson($Res);
	}
	
	/**
	 * 绑定第三方
	 */
	public function oauthBindTwo(){
		$CheckParam	= array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('openid','String',1,$this->Lang['100048'],'100048'),
				array('username','String',1,$this->Lang['100031'],'100031'),
				array('password','String',1,$this->Lang['100032'],'100032'),
				array('repeatpwd','String',1,$this->Lang['100038'],'100038'),
				array('jpushid','String',0),
		);
	
		$BackData 				= $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']			= 'oauthBindTwo';//执行方法名
		$BackData['groupid']	= 3;
		//接口调用
		$BackData['isapi']		= true;//是否为内部接口调用
		$parame					= json_encode($BackData);
		$Res					= $this->Helper($parame, 'User');
		$this->ReturnJson($Res);
	}
	
	/**
	 * 第三方登录
	 */
	public function thirdPartyLogin(){
		$CheckParam	= array(
			array('time','Int',1,$this->Lang['100001'],'100001'),
			array('hash','String',1,$this->Lang['100002'],'100002'),
			array('openid','String',1,$this->errorInfo['100048'],'100048'),
		);
		$BackData 				= $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']			= 'thirdLogin';//执行方法名
		$BackData['groupid']	= 3;
		//接口调用
		$BackData['isapi']		= true;//是否为内部接口调用
		$parame					= json_encode($BackData);
		$Res					= $this->Helper($parame, 'User');
		$this->ReturnJson($Res);
	}
	/**
	 * 短信密码登录
	 */
	public function mobileLogin(){
		$CheckParam	= array(
		array('time','Int',1,$this->Lang['100001'],'100001'),
		array('hash','String',1,$this->Lang['100002'],'100002'),
		array('username','String',1,$this->Lang['100045'],'100045'),
		array('checkcode','String',1,$this->errorInfo['100039'],'100039'),
		array('jpushid','String',0),
		);
		$BackData 				= $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']			= 'mobileMsgLogin';//执行方法名
		//$BackData['groupid']	= 3;
		//接口调用
		$BackData['isapi']		= true;//是否为内部接口调用
		$parame					= json_encode($BackData);
		$Res					= $this->Helper($parame, 'User');
		$this->ReturnJson($Res);
	}
	/**
	 * 手机号是否绑定检验
	 */
	public function checkMobileBind(){
		$CheckParam	= array(
			array('time','Int',1,$this->Lang['100001'],'100001'),
			array('hash','String',1,$this->Lang['100002'],'100002'),
			array('uid','Int',1,$this->Lang['100040'],'100040'),
			array('hashid','String',1,$this->Lang['100041'],'100041'),
		);
		$BackData 				= $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']			= 'checkMobile';//执行方法名
		//接口调用
		$BackData['isapi']		= true;//是否为内部接口调用
		$parame					= json_encode($BackData);
		$Res					= $this->Helper($parame, 'User');
		$this->ReturnJson($Res);
	}
	/**
	 * 手机号是绑定操作
	 */
	public function mobileBind(){
		$CheckParam	= array(
			array('time','Int',1,$this->Lang['100001'],'100001'),
			array('hash','String',1,$this->Lang['100002'],'100002'),
			array('uid','Int',1,$this->Lang['100040'],'100040'),
			array('hashid','String',1,$this->Lang['100041'],'100041'),
			array('mobile','String',1,$this->Lang['100045'],'100045'),
			array('checkcode','String',1,$this->Lang['100039'],'100039'),
		);
		$BackData 				= $this->CheckData(I('request.'),$CheckParam);
		$_REQUEST['username'] 	= $BackData['mobile'];
		//自定义接口参数区
		$BackData['ac']			= 'bindMobile';//执行方法名
		//接口调用
		$BackData['isapi']		= true;//是否为内部接口调用
		$parame					= json_encode($BackData);
		$Res					= $this->Helper($parame, 'User');
		$this->ReturnJson($Res);
	}
	
	/**
	 * 验证hashid
	 */
	public function checkHashid(){
		$CheckParam	= array(
		array('time','Int',1,$this->Lang['100001'],'100001'),
		array('hash','String',1,$this->Lang['100002'],'100002'),
		array('uid','Int',1,$this->Lang['100040'],'100040'),
		array('hashid','String',1,$this->Lang['100041'],'100041'),
		);
		$BackData 				= $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']			= 'checkHashid';//执行方法名
		//接口调用
		$BackData['isapi']		= true;//是否为内部接口调用
		$parame					= json_encode($BackData);
		$Res					= $this->Helper($parame, 'User');
		$this->ReturnJson($Res);
	}
	/**
	 * 短信密码登录
	 */
	public function getSign(){
		$CheckParam	= array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
		);
		$BackData 				= $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']			= 'getSign';//执行方法名
		//接口调用
		$BackData['isapi']		= true;//是否为内部接口调用
		$parame					= json_encode($BackData);
		$Res					= $this->Helper($parame, 'User');
		$this->ReturnJson($Res);
	}
	
}
?>