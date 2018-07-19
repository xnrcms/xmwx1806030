<?php
namespace Api\Helper;
/**
 * 分类数据
 * @author 王远庆
 */
class CategoryHelper extends BaseHelper{
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
	 * Brand列表
	 * @author 王远庆
	 */
	private function categoryNav($Parame){
		$Parame['limit']			= 100;
		$Parame['isrecommend']		= 1;
		$Parame['pid']				= 1;
		$Parame['page']				= 1;
		$list						= $this->categoryList($Parame);
		$data['list']				= empty($list) ? (object)array() : $list;
		$data['total']				= $this->_total;
		$data['remainder']			= $this->_remainder;
		$data['page']				= $Parame['page'];
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	private function categoryList($Parame){
		$limit						= $Parame['limit'] > 0 ? $Parame['limit'] : 10;
		//获取数据
		$MainTab					= 'category';
		$MainAlias					= 'main';
		$MainField					= array('id','pid','name','icon');

		//主表模型
		$MainModel 					= M($MainTab)->alias($MainAlias);

		$RelationTab				= array();
// 		$RelationTab['member']		= array('Ralias'=>'me','Ron'=>'me ON me.uid=main.fid','Rfield'=>array('nickname','face','county','county_name','gender'));
		$tables	  					= $RelationTab['tables'];
		$RelationFields				= $RelationTab['fields'];
		$model						= !empty($tables) ? $MainModel->join ( $tables ,'LEFT') : $MainModel;

		//检索条件
		$map 						= array();
		$map['main.status']			= 1;
		if (isset($Parame['isrecommend'])){
			$map['main.isrecommend']	= $Parame['isrecommend'];
		}
		if (isset($Parame['pid'])){
			$map['main.pid']	= $Parame['pid'];
		}

		//排序
		$order						= $MainAlias.'.sort desc';
		//检索字段
		$fields						= (empty($MainField) ? get_fields_string($MainModel->getDbFields(),$MainAlias).',' : get_fields_string($MainField,$MainAlias).',') . $RelationFields;
		$fields						= trim($fields,',');

		//列表数据
		$list 						= $this->getLists($model,$map,$order,$fields,$Parame['page'],$limit,false);
		if (!empty($list)){
			foreach ($list as $k=>$v){
				//数据格式化
				$list[$k]['id']				= $v['id']*1;
				$list[$k]['pid']			= $v['pid']*1;
				$pic						= intval($v['icon']) > 0 ? get_cover(intval($v['icon']),'path') : '';
				$list[$k]['icon']			= !empty($pic) ? 'http://'.WEB_DOMAIN.$pic : '';
			}
		}
		return $list;
	}
	
	/**
	 * 分类子列表
	 * @author
	 */
	private function categoryChild($Parame){
	    $list					= $this->categoryList($Parame);
	    $data['list']				= empty($list) ? (object)array() : $list;

	    $data['total']			= $this->_total;
	    $data['remainder']			= $this->_remainder;
	    $data['page']			= $Parame['page'];
	    return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}

	/**
	 * 分类名和分类下的商品
	 * @author
	 */
	private function categoryChildList($Parame){
	        $list					= $this->categoryList($Parame);
	    	$data['list']				= empty($list) ? (object)array() : $list;

	    	foreach ($data['list'] as $k => $v) {
	    		$data['list'][$k]['goods'] = M('Goods')->alias('g')
	    						  	  ->join('left join duoduo_picture as p on g.goodsimg=p.id')
					       		  	  ->field('g.id,g.goodsname,g.goodsimg,g.goodsmarkeyprice,g.goodsprice,g.sales,g.joinnum,g.category_id,p.path')
	    						   	  ->where('g.category_id='.$v['id'])
	    						   	  ->select();	
	    		$data['list'][$k]['goodscount'] = count($data['list'][$k]['goods']);
	    		//统计该分类下的销售总量
	    		$data['list'][$k]['salescount'] = 0;				   	  
	    		foreach ($data['list'][$k]['goods'] as  $v) {
				    $data['list'][$k]['salescount'] += $v['sales'];
			     }
	    	}				   	  

	    	$data['total']			= $this->_total;
	    	$data['remainder']		= $this->_remainder;
	    	return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}

}
?>