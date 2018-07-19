<?php
namespace Api\Controller;
use Api\Model;
/**
 * 用户控制器
 * @author 王远庆
 */
class CategoryController extends CommonController {
	public function index(){
		$this->ReturnJson();
	}

	/**
	 * 分类导航
	 * @author 王远庆
	 */
	public function categoryNav(){
		$CheckParam	= array(
		array('time','Int',1,$this->Lang['100001'],'100001'),
		array('hash','String',1,$this->Lang['100002'],'100002'),
		);
		$BackData 				= $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']			= 'categoryNav';//执行方法名
		//接口调用
		$BackData['isapi']		= true;//是否为内部接口调用
		$parame					= json_encode($BackData);
		$Res					= $this->Helper($parame, 'Category');
		$this->ReturnJson($Res);
	}
	
	/** 
	 * 获取子类列表
	 * @author
	 */
	public function categoryChild(){
	    $CheckParam	= array(
	        array('time','Int',1,$this->Lang['100001'],'100001'),
	        array('hash','String',1,$this->Lang['100002'],'100002'),
	        array('pid','Int',1,$this->Lang['100020'],'100020'),
	    );
	    $BackData 				= $this->CheckData(I('request.'),$CheckParam);
	    //自定义接口参数区
	    $BackData['ac']			= 'categoryChild';//执行方法名
	    //接口调用
	    $BackData['isapi']		= true;//是否为内部接口调用
	    $parame					= json_encode($BackData);
	    $Res					= $this->Helper($parame, 'Category');
	    $this->ReturnJson($Res);
	}

	/** 
	 * 获取所有子类列表下的商品
	 * @author
	 */
	public function categoryChildList(){
	    $CheckParam	= array(
	        array('time','Int',1,$this->Lang['100001'],'100001'),
	        array('hash','String',1,$this->Lang['100002'],'100002'),
	        array('pid','Int',1,$this->Lang['100020'],'100020'),
	    );
	    $BackData 				= $this->CheckData(I('request.'),$CheckParam);
	    //自定义接口参数区
	    $BackData['ac']			= 'categoryChildList';//执行方法名
	    //接口调用
	    $BackData['isapi']		= true;//是否为内部接口调用
	    $parame					= json_encode($BackData);
	    $Res					= $this->Helper($parame, 'Category');
	    $this->ReturnJson($Res);
	}


}
?>