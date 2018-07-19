<?php
namespace Api\Controller;

/**
 * 佣金控制器
 */
class CommissionController extends CommonController {
	
	/**
	 * 佣金排行榜
	 */
	public function top(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('mobile','String',0),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'top';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Commission');
		$this->ReturnJson($Res);
	}
	
	/**
	 * 我的助粉
	 */
	public function fans(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'fans';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Commission');
		$this->ReturnJson($Res);
	}	

	/**
	 * 分佣明细
	 */
	public function detail(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('page','Int',1,$this->Lang['100709'],'100709'),
				array('longitude','String', 0),	//经度
				array('latitude','String', 0),	//纬度
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'detail';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Commission');
		$this->ReturnJson($Res);
	}
	
	/**
	 * 分佣详情
	 */
	public function commissionDetail(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('id', 'Int', 1, $this->Lang['101401'], '101401'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'commissionDetail';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Commission');
		$this->ReturnJson($Res);
	}
}
