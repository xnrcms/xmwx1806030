<?php
namespace Api\Helper;
/**
 * 城市
 * @author 王远庆
 */
class AreaHelper extends BaseHelper{
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
	 * 直播省级城市列表
	 * @author 王远庆
	 */
	private function areaList($Parame){
		if (!in_array($Parame['utype'], array(1,2,3))){
			return array('Code' =>'100206','Msg'=>$this->Lang['100206']);
		}
		$limit						= $Parame['limit'] > 0 ? $Parame['limit'] : 10;
		//获取数据
		$MainTab					= 'area';
		$MainAlias					= 'main';
		$MainField					= array('id,area as name');

		//主表模型
		$MainModel 					= M($MainTab)->alias($MainAlias);

		$RelationTab				= array();
		//$RelationTab['member']		= array('Ralias'=>'me','Ron'=>'me ON me.uid=main.fid','Rfield'=>array('nickname','face','county','county_name','gender'));

		$RelationTab				= $this->getRelationTab($RelationTab);
		$tables	  					= $RelationTab['tables'];
		$RelationFields				= $RelationTab['fields'];
		$model						= !empty($tables) ? $MainModel->join ( $tables ,'LEFT') : $MainModel;

		//检索条件
		$map 						= array();
		$map['main.status']			= 1;
		$map['main.level']			= 1;
		$map['main.pid']			= 0;

		//排序
		$order						= $MainAlias.'.id asc';
		//检索字段
		$fields						= (empty($MainField) ? get_fields_string($MainModel->getDbFields(),$MainAlias).',' : get_fields_string($MainField,$MainAlias).',') . $RelationFields;
		$fields						= trim($fields,',');

		//列表数据
		$list 						= $this->getLists($model,$map,$order,$fields,$Parame['page'],$limit,false);
		if (!empty($list)){
			$others					= array(array('id'=>'1','name'=>'热门'));
			$list					= array_merge($others,$list);
			foreach ($list as $k=>$v){
				//数据格式化
				$list[$k]['id']		= $v['id']*1;

				$map				= array();
				$list[$k]['ucount']	= $this->ucount($Parame['utype'],$v['id']);
			}
		}

		$data['list']		= empty($list) ? (object)array() : $list;
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	private function ucount($utype,$areaid){
		$MainTab					= 'direct_seeding_room';
		$MainAlias					= 'main';
		$MainField					= array('id');

		//主表模型
		$MainModel 					= M($MainTab)->alias($MainAlias);

		$RelationTab				= array();
		$RelationTab['member']		= array('Ralias'=>'me','Ron'=>'me ON me.uid=main.uid','Rfield'=>array('gender'));

		$RelationTab				= $this->getRelationTab($RelationTab);
		$tables	  					= $RelationTab['tables'];
		$RelationFields				= $RelationTab['fields'];
		$model						= !empty($tables) ? $MainModel->join ( $tables ,'LEFT') : $MainModel;

		//检索条件
		$map 						= array();
		$map['main.livestatus']		= 1;
		$map['main.last_time']		= array('egt',NOW_TIME);
		$map['main.status']			= 1;
		if (in_array($utype, array(1,2))){
			$map['me.gender']		= $utype;
		}else{
			$map['me.gender']		= array('in','1,2');
		}
		if ($areaid > 100){
			$map['me.province']		= $areaid;
		}

		$counts						= $model->where($map)->count($MainAlias.'.id');
		return $counts*1;
	}
}
?>