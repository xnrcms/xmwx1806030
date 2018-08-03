<?php
namespace Api\Helper;
/**
 * 商家
 */
class ShopHelper extends BaseHelper{
	//初始化接口
	public function apiRun($parame = ''){
		//接口分发
		$Parame		= !empty($parame) ? json_decode($parame,true) : '';
		$ac			= $Parame['ac'];
		$isapi		= $Parame['isapi'];
		if ($isapi === true){
			return !empty($ac) ? $this->$ac($Parame) : array('Code' =>'100009','Msg'=>$this->Lang['100009']);
		}
		return array('Code' =>'100007','Msg'=>$this->Lang['100007']);
	}
	
	/**
	 * 搜索页面
	 */
	private function search($Parame){
		$search = M('search')->order('create_time desc')->group('name')->limit(8)->select();
		$data = array();
		if(!empty($search)){
			foreach ($search as $key=>$value){
				$data[$key] = $value['name'];
			}
		}
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 清空搜索
	 */
	private function searchDel($Parame){
		$res = M('search')->where(array('uid'=>$Parame['uid']))->delete();
		if($res != false){
			return array('Code' =>'0','Msg'=>$this->Lang['100010']);
		}
		return array('Code' =>'1','Msg'=>$this->Lang['100011']);
	}
	
	/**
	 * 商家搜索列表
	 */
	private function searchShopList($Parame){
		$page						= 1;
		$limit						= 5000;
		//获取数据
		$MainTab					= 'shop';
		$MainAlias					= 'main';
		$MainField					= array('id,shop_name,face,longitude,latitude');
		//主表模型
		$MainModel 					= M($MainTab)->alias($MainAlias);
		$RelationTab				= array();
		$RelationTab				= $this->getRelationTab($RelationTab);
		$tables	  					= $RelationTab['tables'];
		$RelationFields				= $RelationTab['fields'];
		$model						= !empty($tables) ? $MainModel->join ( $tables ,'LEFT'): $MainModel;
		//检索条件
		$map 						= array();
		$map['main.status']			= 1;
		//排序
		$order 						= $MainAlias.'.create_time asc';
		//检索字段
		$fields						= (empty($MainField) ? get_fields_string($MainModel->getDbFields(),$MainAlias).',' : get_fields_string($MainField,$MainAlias).',') . $RelationFields;
		$fields						= trim($fields,',');
	
		//列表数据
		$list 						= $this->getLists($model,$map,$order,$fields,$page,$limit,false);
		if (!empty($list)){
			$row = array();
			foreach ($list as $k=>$v){
				//数据格式化
				$distance = getDistanceBetweenPointsNew($Parame['latitude'], $Parame['longitude'], $v['latitude'], $v['longitude']);
				$list[$k]['meters'] = $distance['meters'];
				$list[$k]['distance'] = $distance['meters']>1000?round($distance['meters']/1000,2).'km':round($distance['meters'],2).'m';
				//筛选范围
				if(empty($Parame['distance'])) $Parame['distance'] = 50;
				if($list[$k]['meters'] > $Parame['distance']*1000){
					unset($list[$k]);
					continue;
				}
				$row[$k] = $distance['meters'];
			}
			//if(in_array($Parame['distance'], array(1,3,5))){
				array_multisort($row, SORT_ASC, $list);
			//}
			$list = array_chunk($list, 10);
			$list = $list[$Parame['page']-1];
		}
		$data['list']			= empty($list) ? array() : $list;
		$data['page']			= $Parame['page'];
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 店铺分类
	 */
	private function shopCate($Parame){
		$map 						= array();
		$map['status'] 				= 1;
		if($Parame['pid']>0){
			$map['pid'] 			= $Parame['pid'];
		}else{
			$map['pid'] 			= 0;
			$map['is_recommend'] 	= 1;
		}
		$scategory = M('scategory')->where($map)->order('sort DESC, create_time ASC')->select();
		$data = array();
		if(!empty($scategory)){
			foreach ($scategory as $key=>$value){
				$data[$key]['id'] = $value['id'];
				$data[$key]['name'] = $value['name'];
			}
		}
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 商家列表
	 */
	private function shopList($Parame){
		$page						= 1;
		$limit						= 5000;
		//获取数据
		$MainTab					= 'shop';
		$MainAlias					= 'main';
		$MainField					= array('id,shop_name,face,longitude,latitude');
		//主表模型
		$MainModel 					= M($MainTab)->alias($MainAlias);
		$RelationTab				= array();
		$RelationTab				= $this->getRelationTab($RelationTab);
		$tables	  					= $RelationTab['tables'];
		$RelationFields				= $RelationTab['fields'];
		$model						= !empty($tables) ? $MainModel->join ( $tables ,'LEFT'): $MainModel;
		//检索条件
		$map 						= array();
		$map['main.status']			= 1;
		//定位所在的市
		if($Parame['area_string']){
			$area 					= M('area')->where(array('area'=>$Parame['area_string']))->getField('id');
			if($area){
				$map['area']		= $area;
			}
		}
		//筛选市所在的区
		if($Parame['county']){
			$map['county']			= $Parame['county'];
		}
		
		//排序
		$order 						= $MainAlias.'.create_time asc';
		
		//检索字段
		$fields						= (empty($MainField) ? get_fields_string($MainModel->getDbFields(),$MainAlias).',' : get_fields_string($MainField,$MainAlias).',') . $RelationFields;
		$fields						= trim($fields,',');
	
		//列表数据
		$list 						= $this->getLists($model,$map,$order,$fields,$page,$limit,false);
		
		if (!empty($list)){
			$row = array();
			foreach ($list as $k=>$v){
				//数据格式化
				$distance = getDistanceBetweenPointsNew($Parame['latitude'], $Parame['longitude'], $v['latitude'], $v['longitude']);
				$list[$k]['meters'] = $distance['meters'];
				$list[$k]['distance'] = $distance['meters']>1000?round($distance['meters']/1000,2).'km':round($distance['meters'],2).'m';
				//筛选范围
				/* if(empty($Parame['distance'])) $Parame['distance'] = 50;
				if($list[$k]['meters'] > $Parame['distance']*1000){
					unset($list[$k]);
					continue;
				} */
				$row[$k] = $distance['meters'];
			}
			if($Parame['distance'] == 2){
				array_multisort($row, SORT_DESC, $list);
			}else{
				array_multisort($row, SORT_ASC, $list);
			}
			$list = array_chunk($list, 300);
			$list = $list[$Parame['page']-1];
		}
		$data['list']			= empty($list) ? array() : $list;
		$data['page']			= $Parame['page'];
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 商家店铺详情
	 */
	private function shopDetail($Parame){
		$shop = M('shop')->field('id,shop_name,face,grade,address,mobile,desc,longitude,latitude')->where(array('id'=>$Parame['shop_id']))->find();
		$data = array();
		if(!empty($shop['id'])){
			$data['id'] 			= $shop['id'];
			$data['face'] 			= $shop['face'];
			$data['shop_name'] 		= $shop['shop_name'];
			$data['star'] 			= $shop['grade'];
			$data['mobile'] 		= $shop['mobile'];
			$data['address'] 		= $shop['address'];
			$data['desc'] 			= $shop['desc'];
			$data['longitude'] 		= $shop['longitude'];
			$data['latitude'] 		= $shop['latitude'];
			$data['shop_pic'] 		= M('shop_pic')->field('shop_pic,shop_description')->where(array('shop_id'=>$shop['id']))->select();
		}else{
			return array('Code' =>'101627','Msg'=>'101627');
		}
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 商品列表图片
	 */
	private function goodsListPic($Parame){
		$data 					= array();
		//轮播图
		$carousel 				= M('banner')->where(array('type'=>3, 'status'=>1))->limit('0,3')->select();
		
		$row 					= array();
		if(!empty($carousel)){
			foreach ($carousel as $key=>$value){
				$row[$key]['picture'] 	= $value['picture'];
				$row[$key]['link'] 		= $value['link'];
			}
		}
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$row);
	}
	/**
	 * 商家商品列表
	 */
	private function goodsList($Parame){
	
		$page						= $Parame['page'];
		$limit						= 10;
		//获取数据
		$MainTab					= 'goods';
		$MainAlias					= 'main';
		$MainField					= array('id,goodsname,goodsimg,goodsprice');
	
		//主表模型
		$MainModel 					= M($MainTab)->alias($MainAlias);
	
		$RelationTab				= array();
		$RelationTab				= $this->getRelationTab($RelationTab);
		$tables	  					= $RelationTab['tables'];
		$RelationFields				= $RelationTab['fields'];
		$model						= !empty($tables) ? $MainModel->join ( $tables ,'LEFT'): $MainModel;
	
		//检索条件
		$map 						= array();
		$map['main.status']			= 1;
		//搜索
		if(!empty($Parame['keyword'])){
			$map['main.goodsname']	= array('like', '%' . $Parame['keyword'] . '%');
		}
		//分类
		$category_id                = $Parame['cid'];
		if ($category_id >0){
			$map['category_id']		= $category_id;
		}
	
		//排序
		$order						= $MainAlias.'.id ASC';
		
		//检索字段
		$fields						= (empty($MainField) ? get_fields_string($MainModel->getDbFields(),$MainAlias).',' : get_fields_string($MainField,$MainAlias).',') . $RelationFields;
		$fields						= trim($fields,',');
	
		//列表数据
		$list 						= $this->getLists($model,$map,$order,$fields,$page,$limit,false);
		if (!empty($list)){
			foreach ($list as $k=>$v){
				//数据格式化
			}
		}
		$data['list']			= empty($list) ? array() : $list;
		$data['page']			= $Parame['page'];
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 商家商品详情
	 */
	private function goodsDetail($Parame){
		
		//商品id
		$gid 			= intval($Parame['goods_id']);
		
		$goods = M('goods')->where(array('id'=>$Parame['goods_id'], 'status'=>1))->find();
		$data = array();
		if(!empty($goods)){
			//热门加1
			M('goods')->where(array('id'=>$goods['id']))->setInc('hit');
			//数据
			$data['pics'] 					= explode(',', $goods['goodsimgs']);
			$data['goodsname'] 				= $goods['goodsname'];
			$data['goodsprice'] 			= $goods['goodsprice'];
			$data['originalprice'] 			= $goods['originalprice'];
			$data['content'] 				= $goods['content'];
			$data['goodsimg'] 				= $goods['goodsimg'];
			$data['stock'] 					= $goods['stock'];
			$goodsParameters 				= M('goods_parameters')->field('pname,pvalue')->where(array('gid'=>$goods['id']))->order('sort desc')->select();
			$data['goods_parameters'] 		= $goodsParameters;
		}
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	
	/**
	 * 收藏商品
	 */
	public function goodsCollection($Parame) {
		//用户id
		$uid 			= intval($Parame['uid']);
		//商品id
		$gid 			= intval($Parame['gid']);
		$info 			= M('collection')->where(array('gid' => $gid, 'uid' => $uid, 'type' => 1))->find();
		if (!empty($info) && $info['status']==1) {
			$qcollection = M('collection')->where(array('gid' => $gid, 'uid' => $uid, 'type' => 1))->save(array('status'=>2));
			if($qcollection != false){
				return array('Code' =>'0','Msg'=>$this->Lang['101720']);
			}
		}elseif(!empty($info) && $info['status']==2){
			$qcollection = M('collection')->where(array('gid' => $gid, 'uid' => $uid, 'type' => 1))->save(array('status'=>1));
			if($qcollection != false){
				return array('Code' =>'0','Msg'=>$this->Lang['101721']);
			}
		}else {
			$data = array(
					'type' => 1,
					'gid' => $gid,
					'uid' => $uid,
					'status' => 1,
					'create_time' => NOW_TIME
			);
			$cinfo = M('collection')->data($data)->add();
			if ($cinfo > 0) {
				return array('Code' =>'0','Msg'=>$this->Lang['101721']);
			} else {
				return array('Code' =>'101722','Msg'=>$this->Lang['101722']);
			}
		}
	}
	
	/**
	 * 分享接口
	 */
	private function share($Parame){
		$shop = M('shop')->where(array('id'=>$Parame['shop_id'], 'status'=>1))->find();
		$data = array();
		if(!empty($shop)){
			//分享链接
			$data['url'] 				= 'http://'.WEB_DOMAIN.'/Home/Index/share/shop_id/'.$Parame['shop_id'].'/pid/'.$Parame['uid'].'.html';
			//分享图标
			$data['img'] 				= $shop['share_icon'];
			//分享图片
			$data['big_img'] 			= $shop['share_pic'];
			//分享标题
			$data['title'] 				= $shop['shop_name'];
			//分享内容
			$data['content'] 			= M('platform_config')->where(array('name'=>'SHARE_CONTENT'))->getField('value');
		}
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
}
?>