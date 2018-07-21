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
	
	//签到
	public function sign(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'sign';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Index');
		$this->ReturnJson($Res);
	}
	
	//兑领红包
	public function redPacket(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'redPacket';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Index');
		$this->ReturnJson($Res);
	}
	
	//业务项目
	public function ywxm(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'ywxm';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Index');
		$this->ReturnJson($Res);
	}
	
	//链接信息
	public function urlInfo(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',0, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 0, $this->Lang['100005'], '100005'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		$data = array(
			'ptgf_url'					=> 'http://'.WEB_DOMAIN.'/Home/Index/article/type/1.html',			//平台规范
			'zsbz_url'					=> 'http://'.WEB_DOMAIN.'/Home/Index/article/type/2.html',			//招商标准
			'rzsx_url'					=> 'http://'.WEB_DOMAIN.'/Home/Index/article/type/3.html',			//入驻事项
			'thhgzlc_url'				=> 'http://'.WEB_DOMAIN.'/Home/Index/article/type/4.html',			//退换货规则流程
			'tjhy_url'					=> 'http://'.WEB_DOMAIN.'/Home/Index/invite/pid/'.$BackData['uid'].'.html',			//推荐好友
			'protocol_url'				=> 'http://'.WEB_DOMAIN.'/Home/Index/article/type/8.html',			//服务协议
			'rzsm_url'					=> 'http://'.WEB_DOMAIN.'/Home/Index/article/type/9.html',			//入驻说明
			'ptxy_url'					=> 'http://'.WEB_DOMAIN.'/Home/Index/article/type/10.html',			//平台协议
			'zpxz_url'					=> 'http://'.WEB_DOMAIN.'/Home/Index/article/type/11.html',			//招聘须知
		);
		$Res = array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
		$this->ReturnJson($Res);
	}
	
	//页面底部广告
	public function bottomAd(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'bottomAd';//执行方法名
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
