<?php
namespace Api\Controller;

/**
 * 首页控制器
 */
class OrderController extends CommonController {
	public function index(){
		$this->ReturnJson();
	}
	
	/**
	 * 下单
	 */
	public function order(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('type', 'Int', 1, $this->Lang['101703'], '101703'),
				array('info', 'String', 0, $this->Lang['101707'], '101707'),
				array('cartId', 'String', 0, $this->Lang['101708'], '101708'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'order';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Order');
		$this->ReturnJson($Res);
	}
	
	/**
	 * 提交订单
	 */
	public function orderSubmit(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('shopId', 'Int', 1, $this->Lang['101712'], '101712'),
				array('type', 'Int', 1, $this->Lang['101703'], '101703'),
				array('info', 'String', 1, $this->Lang['101707'], '101707'),
				array('rname', 'String', 1, $this->Lang['101733'], '101733'),
				array('phone', 'String', 1, $this->Lang['101734'], '101734'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'orderSubmit';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Order');
		$this->ReturnJson($Res);
	}
	
	/**
	 * 订单详情
	 */
	public function orderDetail(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('id', 'String', 1, $this->Lang['1017290'], '1017290'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'orderDetail';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Order');
		$this->ReturnJson($Res);
	}
	
	/**
	 * 订单列表
	 */
	public function orderList(){
		$CheckParam	= array(
	        array('time','Int',1,$this->Lang['100001'],'100001'),
			array('hash','String',1,$this->Lang['100002'],'100002'),
			array('hashid','String',1, $this->Lang['100041'],'100041'),
			array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
			array('type', 'Int', 0, $this->Lang['101728'], '101728'),
	        array('page','Int',1,$this->Lang['100709'],'100709'),
		);
	    $BackData 				= $this->CheckData(I('request.'),$CheckParam);
	    //自定义接口参数区
	    $BackData['ac']			= 'orderList';//执行方法名
	    //接口调用
	    $BackData['isapi']		= true;//是否为内部接口调用
	    $parame					= json_encode($BackData);
	    $Res					= $this->Helper($parame, 'Order');
	    $this->ReturnJson($Res);
	}
	
	/**
	 * 取消订单
	 */
	public function orderCancel(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('id', 'String', 1, $this->Lang['101729'], '101729'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'orderCancel';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Order');
		$this->ReturnJson($Res);
	}
	
	/**
	 * 确认订单
	 */
	public function orderConfirm(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('id', 'String', 1, $this->Lang['101729'], '101729'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'orderConfirm';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Order');
		$this->ReturnJson($Res);
	}
	
	/**
	 * 催促发货
	 */
	public function orderNotice(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('id', 'String', 1, $this->Lang['101729'], '101729'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'orderNotice';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Order');
		$this->ReturnJson($Res);
	}
	
	/**
	 * 查看物流
	 */
	public function orderLogistics(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('id', 'String', 1, $this->Lang['101729'], '101729'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'orderLogistics';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Order');
		$this->ReturnJson($Res);
	}
	
	/**
	 * 订单退款页面
	 */
	public function orderRefund(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('id', 'String', 1, $this->Lang['101729'], '101729'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'orderRefund';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Order');
		$this->ReturnJson($Res);
	}
	
	/**
	 * 订单退款提交
	 */
	public function orderRefundSubmit(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('id', 'String', 1, $this->Lang['101729'], '101729'),
				array('explain', 'String', 0, $this->Lang['101730'], '101730'),
				array('pic', 'String', 0, $this->Lang['101731'], '101731'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'orderRefundSubmit';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Order');
		$this->ReturnJson($Res);
	}
	
	/**
	 * 订单评价
	 */
	public function orderEvaluate(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('shop_id', 'Int', 1, $this->Lang['101201'], '101201'),
				array('score', 'Int', 1, $this->Lang['100028'], '100028'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'orderEvaluate';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Order');
		$this->ReturnJson($Res);
	}
		
}
