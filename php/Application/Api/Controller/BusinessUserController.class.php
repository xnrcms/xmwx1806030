<?php
namespace Api\Controller;

/**
 * 首页控制器
 */
class BusinessUserController extends CommonController {
	
	//商家中心首页
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
		$Res                    = $this->Helper($parame, 'BusinessUser');
		$this->ReturnJson($Res);
	}
	
	//商家申请入驻接口
	public function apply(){
		$CheckParam = array(
			array('time','Int',1,$this->Lang['100001'],'100001'),
			array('hash','String',1,$this->Lang['100002'],'100002'),
			//开户人
			array('account_person', 'String', 1, $this->Lang['101601'], '101601'),
			//银行账号
			array('bank_account', 'String', 1, $this->Lang['101602'], '101602'),
			//负责人
			array('name', 'String', 1, $this->Lang['101603'], '101603'),
			//营业电话
			array('mobile', 'String', 1, $this->Lang['101621'], '101621'),
			//商户类型
			array('category_id', 'String', 1, $this->Lang['101605'], '101605'),
			//提拨比例
			array('proportion', 'String', 1, $this->Lang['101606'], '101606'),
			//省
			array('province', 'String', 1, $this->Lang['101607'], '101607'),
			//市
			array('area', 'String', 1, $this->Lang['101608'], '101608'),
			//区
			array('county', 'String', 1, $this->Lang['1016081'], '1016081'),
			//详细地址
			array('address', 'String', 1, $this->Lang['101609'], '101609'),
			//经度
			array('longitude', 'String', 1, $this->Lang['101629'], '101629'),
			//纬度
			array('latitude', 'String', 1, $this->Lang['101630'], '101630'),
			//账号
			array('phone', 'String', 1, $this->Lang['101604'], '101604'),
			//密码
			array('password', 'String', 1, $this->Lang['101610'], '101610'),
			//邀请码
			array('code', 'String', 1, $this->Lang['101622'], '101622'),
			//证件类型
			array('certificates_type', 'String', 1, $this->Lang['101611'], '101611'),
			//证件姓名
			array('document_name', 'String', 1, $this->Lang['101612'], '101612'),
			//证件号
			array('certificates_number', 'String', 1, $this->Lang['101613'], '101613'),
			//正面照片
			array('ID_front_img', 'String', 1, $this->Lang['101614'], '101614'),
			//反面照片
			array('ID_back_img', 'String', 1, $this->Lang['101615'], '101615'),
			//营业执照
			array('license_img', 'String', 1, $this->Lang['101616'], '101616'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'apply';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'BusinessUser');
		$this->ReturnJson($Res);
	}
	
	//资料编辑详情
	public function shopInfo(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'shopInfo';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'BusinessUser');
		$this->ReturnJson($Res);
	}
	
	//资料编辑修改
	public function shopInfoSubmit(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				//商家名称
				array('shop_name', 'String', 1, $this->Lang['101203'], '101203'),
				//商家类型
				array('category_id', 'Int', 1, $this->Lang['101205'], '101205'),
				//经度
				array('longitude', 'String', 1, $this->Lang['101629'], '101629'),
				//纬度
				array('latitude', 'String', 1, $this->Lang['101630'], '101630'),
				//省
				array('province', 'String', 1, $this->Lang['101607'], '101607'),
				//市
				array('area', 'String', 1, $this->Lang['101608'], '101608'),
				//区
				array('county', 'String', 1, $this->Lang['1016081'], '1016081'),
				//详细地址
				array('address', 'String', 1, $this->Lang['101609'], '101609'),
				//店家详情介绍
				array('desc', 'String', 1, $this->Lang['101617'], '101617'),
				//店头照片
				array('face', 'String', 1, $this->Lang['101618'], '101618'),
				//店家照片
				array('shop_pic', 'String', 1, $this->Lang['101620'], '101620'),
				//收款码
				//array('receipt_code', 'String', 1, $this->Lang['101619'], '101619'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'shopInfoSubmit';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'BusinessUser');
		$this->ReturnJson($Res);
	}
	
	
	//商家分类
	public function scategory(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
		);
		$BackData 	= $this->CheckData(I('request.'),$CheckParam);
		$list 		= M('scategory')->field('id,name,pid')->where(array('status'=>1))->order('sort desc,id asc')->select();
		$list 		= list_to_tree($list, $pk = 'id', $pid = 'pid', $child = 'list', $root = 0);
		$Res = array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$list);
		$this->ReturnJson($Res);
	}

	
	//财务信息
	public function financeDetail(){
		$CheckParam	= array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'financeDetail';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'BusinessUser');
		$this->ReturnJson($Res);
	}
	
	//财务信息列表
	public function financeList(){
		$CheckParam	= array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('page','Int',1,$this->Lang['100709'],'100709'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'financeList';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'BusinessUser');
		$this->ReturnJson($Res);
	}
	
	/**
	 * 提现
	 */
	public function withdraw(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('money', 'String', 1, $this->Lang['101623'], '101623'),
				array('card_id', 'Int', 1, $this->Lang['101625'], '101625'),
				array('proportion', 'String', 1, $this->Lang['101624'], '101624'),
				
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'withdraw';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'BusinessUser');
		$this->ReturnJson($Res);
	}
	
	//我的店家
	public function store(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'store';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'BusinessUser');
		$this->ReturnJson($Res);
	}
	
	//我的店家提交
	public function storeSubmit(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('id', 'Int', 1, $this->Lang['101626'], '101626'),
				array('xinlidou', 'String', 0, $this->Lang['101725'], '101725'),
				array('xianglidou', 'String', 0, $this->Lang['101726'], '101726'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'storeSubmit';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'BusinessUser');
		$this->ReturnJson($Res);
	}
	

	/**
	 * 线下订单提交
	 */
	public function orderSubmit(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('shop_id', 'Int', 1, $this->Lang['101826'], '101826'),
				array('money', 'String', 1, $this->Lang['101628'], '101628'),
				array('xinlidou', 'String', 0, $this->Lang['101725'], '101725'),
				array('xianglidou', 'String', 0, $this->Lang['101726'], '101726'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'orderSubmit';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'BusinessUser');
		$this->ReturnJson($Res);
	}
	
	
	
}
