<?php
namespace Api\Controller;

/**
 * 首页控制器
 */
class IndexController extends CommonController {
	
	public function index(){
		$CheckParam = array(
			array('time','Int',1,$this->Lang['100001'],'100001'),
			array('hash','String',1,$this->Lang['100002'],'100002'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
     	$BackData['ac']         = 'index';//执行方法名
      	//接口调用
     	$BackData['isapi']      = true;//是否为内部接口调用
     	$parame                 = json_encode($BackData);
     	$Res                    = $this->Helper($parame, 'Index');
     	
     	
     	
    	$this->ReturnJson($Res);
	}
	
	//消息列表
	public function message(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('page','Int',1,$this->Lang['100709'],'100709'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'message';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Index');
		$this->ReturnJson($Res);
	}
	
	//删除消息
	public function messageDel(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('id', 'Int', 1, $this->Lang['101723'], '101723'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'messageDel';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Index');
		$this->ReturnJson($Res);
	}
	
	//获取地理位置
	public function getLocation(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('url','String',1,$this->Lang['101632'],'101632'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'getLocation';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Index');
		$this->ReturnJson($Res);
	}
	
	//用户授权
	public function auth(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				//array('openId','String',1,$this->Lang['1'],'1'),
				array('backUrl','String',0,$this->Lang['1'],'1'),
				array('code','String',0,$this->Lang['1'],'1'),
				
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'auth';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Index');
		$this->ReturnJson($Res);
	}
	
	//获取用户坐标
	public function getCoordinate(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',0, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 0, $this->Lang['100005'], '100005'),
				array('longitude','String', 1,$this->Lang['101214'],'101214'),	//经度
				array('latitude','String', 1,$this->Lang['101215'],'101215'),	//纬度
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'getCoordinate';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Index');
		$this->ReturnJson($Res);
	}
     
}
