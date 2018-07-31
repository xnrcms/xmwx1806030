<?php
namespace Api\Controller;
/**
 * 支付控制器
 */
class PayController extends CommonController {
	
	
	
	
	public function index(){
		//自定义接口参数区
     	$BackData['ac']         = 'updateOrder';//执行方法名
     	//接口调用
     	$BackData['isapi']      = true;	//是否为内部接口调用
     	$parame                 = json_encode($BackData);
     	$Res                    = $this->Helper($parame, 'Pay');
     	
    	$this->ReturnJson($Res);
	
	}
	

	/**
	 * 支付接口
	 * @author 王振
	 */
	public function pay(){
		$CheckParam	= array(
			array('time','Int',1,$this->Lang['100001'],'100001'),
			array('hash','String',1,$this->Lang['100002'],'100002'),
			array('hashid','String',1, $this->Lang['100041'],'100041'),
			array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
			array('order_id','Int',1,$this->Lang['1017290'],'1017290'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
     	$BackData['ac']         = 'pay';//执行方法名
      	//接口调用
     	$BackData['isapi']      = true;	//是否为内部接口调用
     	$parame                 = json_encode($BackData);
     	$Res                    = $this->Helper($parame, 'Pay');
    	$this->ReturnJson($Res);
	}
	
	/**
	 * 
	 */
	public function paySuccess(){
		//自定义接口参数区
		$BackData['ac']			= 'paySuccess';//执行方法名
		//接口调用
		$BackData['isapi']		= true;//是否为内部接口调用
		$parame					= json_encode($BackData);
		$Res					= $this->Helper($parame, 'Pay');
		$this->ReturnJson($Res);
	}
}
?>