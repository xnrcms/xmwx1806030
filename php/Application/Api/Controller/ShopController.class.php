<?php
namespace Api\Controller;

/**
 * 首页控制器
 */
class ShopController extends CommonController {
	
	//搜索页面
	public function search(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'search';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Shop');
		$this->ReturnJson($Res);
	}
	
	//清空搜索
	public function searchDel(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'searchDel';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Shop');
		$this->ReturnJson($Res);
	}
	
	//搜索列表
	public function searchList(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('keyword', 'String', 1, $this->Lang['100111'], '100111'),
				array('type', 'Int', 0, $this->Lang['101212'], '101212'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'searchShopList';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Shop');
		$this->ReturnJson($Res);
	}
	
	//获取店铺分类
	public function shopCate(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('pid','Int',0),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'shopCate';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Shop');
		$this->ReturnJson($Res);
	}
	
	//商家列表
	public function shopList(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('page','Int',1,$this->Lang['100709'],'100709'),
				array('cid','Int', 0,$this->Lang['101213'],'101213'),
				array('keyword','String', 0,$this->Lang['101216'],'101216'),
				//array('create_time','Int', 0),			//时间排序 1降序 2升序
				//array('score','Int', 0),		//评分排序 1降序 2升序
				//array('distance','Int', 0),		//距离筛选  1 3 5 50
				array('longitude','String', 1,$this->Lang['101214'],'101214'),	//经度
				array('latitude','String', 1,$this->Lang['101215'],'101215'),	//纬度
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'shopList';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Shop');
		$this->ReturnJson($Res);
	}

	//商家详情
	public function shopDetail(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('shop_id','Int',1,$this->Lang['101201'],'101201'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'shopDetail';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Shop');
		$this->ReturnJson($Res);
	}
	
	//商品分类
	public function category(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
		);
		$BackData 	= $this->CheckData(I('request.'),$CheckParam);
		$list 		= M('category')->field('id,name,pid')->where(array('status'=>1))->order('sort desc,id asc')->select();
		$list 		= list_to_tree($list, $pk = 'id', $pid = 'pid', $child = 'list', $root = 0);
		$Res = array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$list);
		$this->ReturnJson($Res);
	}
	
	//商品列表图片
	public function goodsListPic(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'goodsListPic';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Shop');
		$this->ReturnJson($Res);
	}
	
	//商品列表
	public function goodsList(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('page','Int',1,$this->Lang['100708'],'100708'),
				array('cid','Int',0,$this->Lang['101701'],'101701'),
				array('keyword','String', 0,$this->Lang['101216'],'101216'),
				
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'goodsList';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Shop');
		$this->ReturnJson($Res);
	}
	
	//商品详情
	public function goodsDetail(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('goods_id','Int',1,$this->Lang['101202'],'101202'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'goodsDetail';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Shop');
		$this->ReturnJson($Res);
	}
	
	//收藏商品
	public function goodsCollection(){
		$CheckParam = array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('gid', 'Int', 1, $this->Lang['101704'], '101704'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'goodsCollection';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Shop');
		$this->ReturnJson($Res);
	}
	
	//分享接口
	public function share(){
		$CheckParam	= array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('hashid','String',1, $this->Lang['100041'],'100041'),
				array('uid', 'Int', 1, $this->Lang['100005'], '100005'),
				array('shop_id', 'Int', 1, $this->Lang['101201'], '101201'),
		);
		$BackData               = $this->CheckData(I('request.'),$CheckParam);
		//自定义接口参数区
		$BackData['ac']         = 'share';//执行方法名
		//接口调用
		$BackData['isapi']      = true;//是否为内部接口调用
		$parame                 = json_encode($BackData);
		$Res                    = $this->Helper($parame, 'Shop');
		$this->ReturnJson($Res);
	}
	
}
