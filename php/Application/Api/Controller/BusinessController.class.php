<?php
namespace Api\Controller;

/**
 * 首页控制器
 */
class BusinessController extends CommonController {
	public function index(){
		$this->ReturnJson();
	}
	
	/**
	 * 商家登录
	 */
	public function login(){
		$CheckParam	= array(
			array('time','Int',1,$this->Lang['100001'],'100001'),
			array('hash','String',1,$this->Lang['100002'],'100002'),
			array('username','String',1,$this->Lang['100031'],'100031'),
			array('password','String',1,$this->Lang['100032'],'100032'),
		);
		$BackData 				= $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']			= 'login';//执行方法名
		//接口调用
		$BackData['isapi']		= true;//是否为内部接口调用
		$parame					= json_encode($BackData);
		$Res					= $this->Helper($parame, 'Business');
		$this->ReturnJson($Res);
	}
	
	/**
	 * 订单详情
	 */
	public function orderDetail(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('id', 'String', 1, $this->Lang['1017290'], '1017290'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'orderDetail';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Business');
		$this->ReturnJson($Res);
	}
	
	/**
	 * 订单列表
	 */
	public function orderList(){
		$CheckParam	= array(
	        array('time','Int',1,$this->Lang['100001'],'100001'),
			array('hash','String',1,$this->Lang['100002'],'100002'),
			array('shop_id', 'Int', 1, $this->Lang['101826'], '101826'),
			array('type', 'Int', 0, $this->Lang['101735'], '101735'),
			array('keyword', 'Int', 0, $this->Lang['101604'], '101604'),
	        array('page','Int',1,$this->Lang['100709'],'100709'),
		);
	    $BackData 				= $this->CheckData(I('request.'),$CheckParam);
	    //自定义接口参数区
	    $BackData['ac']			= 'orderList';//执行方法名
	    //接口调用
	    $BackData['isapi']		= true;//是否为内部接口调用
	    $parame					= json_encode($BackData);
	    $Res					= $this->Helper($parame, 'Business');
	    $this->ReturnJson($Res);
	}
	
	/**
	 * 发送信息
	 */
	public function sendMessage(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('id', 'String', 1, $this->Lang['101729'], '101729'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'sendMessage';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Business');
		$this->ReturnJson($Res);
	}
}
