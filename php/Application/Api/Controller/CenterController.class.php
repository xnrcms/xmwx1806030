<?php
namespace Api\Controller;

/**
 * 首页控制器
 */
class CenterController extends CommonController {
	
	//个人中心首页
	public function index(){
		$CheckParam	= array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'index';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	//鑫利豆
	public function xinlidou(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('page','Int',1,$this->Lang['100709'],'100709'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'xinlidou';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	//享利豆 福利豆
	public function xindouku(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('type', 'Int', 1, $this->Lang['101824'], '101824'),
				array('page','Int',1,$this->Lang['100709'],'100709'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'xindouku';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	//个人信息
	public function manager(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'manager';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	//个人信息修改
	public function managerSubmit(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('nickname', 'String', 0, $this->Lang['101800'], '101800'),
				array('phone', 'String', 0, $this->Lang['101801'], '101801'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'managerSubmit';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	//我的收藏
	public function goodsCollectionList(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('page','Int',1,$this->Lang['100709'],'100709'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'goodsCollectionList';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	//浏览历史
	public function goodsHistoryList(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('page','Int',1,$this->Lang['100709'],'100709'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'goodsHistoryList';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	//添加和修改地址
	public function editAddress(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('id','Int',0,$this->Lang['100088'],'100088'),
				array('name','String',1,$this->Lang['100085'],'100085'),
				array('phone','String',1,$this->Lang['100087'],'100087'),
				array('province','String',1,$this->Lang['100089'],'100089'),
				array('city','String',1,$this->Lang['100090'],'100090'),
				array('county','String',1,$this->Lang['100091'],'100091'),
				array('address','String',1,$this->Lang['100084'],'100084'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'editAddress';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	//删除地址
	public function delAddress(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('id', 'Int', 1, $this->Lang['100088'], '100088'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'delAddress';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	//设置默认地址
	public function setAddress(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('id', 'Int', 1, $this->Lang['100088'], '100088'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'setAddress';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	//地址列表
	public function addressList(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('page','Int',1,$this->Lang['100709'],'100709'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'addressList';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	//获取招聘区域
	public function jobCounty(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('city','String',1,$this->Lang['100090'],'100090'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'jobCounty';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	//发布招聘
	public function addJob(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('county','String',1,$this->Lang['101800'],'101800'),
				array('end_time','Int',1,$this->Lang['101801'],'101801'),
				array('company','String',1,$this->Lang['101802'],'101802'),
				array('company_logo','String',1,$this->Lang['101809'],'101809'),
				array('address','String',1,$this->Lang['101803'],'101803'),
				array('position','String',1,$this->Lang['101804'],'101804'),
				array('number','Int',1,$this->Lang['101805'],'101805'),
				array('wages','String',1,$this->Lang['101806'],'101806'),
				array('content','String',1,$this->Lang['101807'],'101807'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'addJob';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	//我的招聘
	public function userJob(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('page','Int',1,$this->Lang['100709'],'100709'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'userJob';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	//招聘详情
	public function jobDetail(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('id', 'Int', 1, $this->Lang['101808'], '101808'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'jobDetail';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	//求才讯息
	public function jobList(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('area','Int',0,$this->Lang['100091'],'100091'),
				array('page','Int',1,$this->Lang['100709'],'100709'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'jobList';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	//获取视频单价
	public function getVideoPrice(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('num','Int',1,$this->Lang['101812'],'101812'),
				array('video_time','Int',1,$this->Lang['101815'],'101815'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'getVideoPrice';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	//发布商家视频
	public function addVideo(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('num','Int',1,$this->Lang['101812'],'101812'),
				array('price','String',1,$this->Lang['101813'],'101813'),
				array('show_time','Int',1,$this->Lang['101814'],'101814'),
				array('video_time','Int',1,$this->Lang['101815'],'101815'),
				array('business_telephone','String',1,$this->Lang['101817'],'101817'),
				array('address','String',1,$this->Lang['101818'],'101818'),
				array('title','String',1,$this->Lang['101819'],'101819'),
				array('content','String',1,$this->Lang['101820'],'101820'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'addVideo';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	//获取广告信息
	public function getAdvertisementInfo(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('shop_id','Int',1,$this->Lang['101826'],'101826'),
				array('mode','String',1,$this->Lang['101811'],'101811'),
				array('show_time','String',1,$this->Lang['101814'],'101814'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'getAdvertisementInfo';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	//发布红包广告
	public function addAdvertisement(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('shop_id','Int',1,$this->Lang['101826'],'101826'),
				array('mode','String',1,$this->Lang['101811'],'101811'),
				array('show_time','String',1,$this->Lang['101814'],'101814'),
				array('num','Int',1,$this->Lang['101812'],'101812'),
				array('price','String',1,$this->Lang['101813'],'101813'),
				array('design_type','Int',1,$this->Lang['101821'],'101821'),
				array('content','String',1,$this->Lang['101820'],'101820'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'addAdvertisement';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	//历史消息
	public function historyList(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('page','Int',1,$this->Lang['100709'],'100709'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'historyList';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	//推广订单详情
	public function spreadDetail(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('type', 'Int', 1, $this->Lang['101828'], '101828'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'spreadDetail';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	//推广订单状态
	public function spreadStatus(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'spreadStatus';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	//商家视频和让利推广订单提交
	public function adOrderSubmit(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('id', 'Int', 1, $this->Lang['101832'], '101832'),
				array('type_id', 'Int', 1, $this->Lang['101828'], '101828'),
				array('xinlidou', 'String', 0, $this->Lang['101725'], '101725'),
				array('xianglidou', 'String', 0, $this->Lang['101726'], '101726'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'adOrderSubmit';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	//视频详情
	public function videoDetail(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('id', 'Int', 1, $this->Lang['101823'], '101823'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'videoDetail';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	//看视频获取豆子
	public function getXindou(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('id', 'Int', 1, $this->Lang['101823'], '101823'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'getXindou';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	//视频列表
	public function videoList(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('page','Int',1,$this->Lang['100709'],'100709'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'videoList';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	/**
	 * 银行卡列表
	 */
	public function bankList(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('page','Int',1,$this->Lang['100709'],'100709'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'bankList';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	/**
	 * 获取银行卡分类
	 */
	public function bankType(){
		$CheckParam = array(
				array('time','Int',1, $this->Lang['100001'],'100001'),
				array('hash','String',1, $this->Lang['100002'],'100002'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'bankType';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	/**
	 * 添加银行卡
	 */
	public function addBank(){
		$CheckParam = array(
				array('time','Int',1, $this->Lang['100001'],'100001'),
				array('hash','String',1, $this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('bank_id', 'int', 1, $this->Lang['101304'], '101304'),
				array('card_number', 'String', 1, $this->Lang['101302'], '101302'),
				array('phone', 'String', 1, $this->Lang['101311'], '101311'),
				array('ID_card', 'String', 1, $this->Lang['101310'], '101310'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'addBank';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	/**
	 * 删除银行卡
	 */
	public function delBank(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('id', 'Int', 1, $this->Lang['101304'], '101304'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'delBank';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	
	
	
	
	
	/**
	 * 交易记录
	 */
	public function transactionRecord(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('page','Int',1,$this->Lang['100709'],'100709'),
				array('type', 'Int', 0, $this->Lang['101501'], '101501'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'transactionRecord';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	/**
	 * 客服中心
	 */
	public function service(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'service';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	/**
	 * 服务调查
	 */
	public function addFeedback(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('content', 'String', 1, $this->Lang['100901'], '100901'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'addFeedback';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	/**
	 * 关于我们
	 */
	public function about(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'about';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Center');
		$this->ReturnJson($Res);
	}
	
	/**
	 * 上传头像
	 */
	public function uploadAvatar(){
		$CheckParam	= array(
				array('time', 'Int', 1, $this->Lang['100001'], '100001'),
				array('hash', 'String', 1, $this->Lang['100002'], '100002'),
				array('hashid','String',1, $this->Lang['100050'],'100050'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('uploadname','String',1,$this->Lang['100011'],'100011')
		);
		$BackData = $this->CheckData(I('request.'), $CheckParam);
		 
		/* 调用文件上传组件上传文件 */
		$Picture 		= D('Picture');
		$pic_driver 	= C('PICTURE_UPLOAD_DRIVER');
		//TODO:上传到远程服务器
		$info 			= $Picture->upload($_FILES,C('PICTURE_UPLOAD'),C('PICTURE_UPLOAD_DRIVER'),C("UPLOAD_".$pic_driver."_CONFIG"));
		/* 记录图片信息 */
		if($info[$BackData['uploadname']]){
			foreach ($info[$BackData['uploadname']] as $k=>$v){
				if ($k == 'path'){
					$info[$BackData['uploadname']][$k] = 'http://'.WEB_DOMAIN.$v;
					$info[$BackData['uploadname']]['avatar'] = $v;
				}else{
					unset($info[$BackData['uploadname']][$k]);
				}
			}
			$this->ReturnJson(array('Code' =>'0','Msg'=>'成功','Data'=>$info[$BackData['uploadname']]));
		} else {
			$this->ReturnJson(array('Code' =>'1','Msg'=>$Picture->getError()));
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

}
