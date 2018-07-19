<?php
namespace Api\Helper;
use Think\Controller;
/**
 * Helper基本
 */
class BaseHelper extends Controller{
	protected function _initialize(){
		$this->videoUrl		= 'rtmp://pili-live-rtmp.tuchewang.com';
	}
	//用户ID校验
	protected function check_user($uid,$hashid){
		//校验用户是否存在
		$user 					= new \User\Api\UserApi();
		$userinfo				= $user->info($uid);
		if ($userinfo == '-1'){
			return array('Code' =>'100005','Msg'=>$this->Lang['100005']);
		}
		$time 					= M('member')->where(array('uid'=>$uid))->getField('last_login_time');
		if (md5($uid.C('DATA_AUTH_KEY').$time) !== $hashid){
			return array('Code' =>'100004','Msg'=>$this->Lang['100004']);
		}
		return array('Code' =>'0','Msg'=>$this->Lang['100013']);
	}
	/* //生成hashid
	protected function create_hashid($uid){
		$time 				= NOW_TIME;
		M('member')->where(array('uid'=>$uid))->save(array('last_login_time'=>$time));
		return md5($uid.C('DATA_AUTH_KEY').$time);
	} */
	//生成hashid
	protected function create_hashid($uid){
		$time 				= NOW_TIME;
		M('user')->where(array('id'=>$uid))->save(array('last_login_time'=>$time));
		return md5($uid.C('DATA_AUTH_KEY').$time);
	}
	//生成校验串
	protected function make_hashid($str){
		return md5($str.C('DATA_AUTH_KEY'));
	}
	//通用分页列表数据集获取方法
	protected function getLists ($model,$where=array(),$order='',$field=true,$page=1,$limit=0,$ispage=false){
		$options    = array();
		$REQUEST    = (array)I('request.');
		//数据对象初始化
		$model  	= is_string($model) ? M($model) : $model;
		$OPT        = new \ReflectionProperty($model,'options');
		$OPT->setAccessible(true);
		//获取主键
		$pk         = $model->getPk();
		if($order===null){
			//order置空
		}elseif( $order==='' && empty($options['order']) && !empty($pk) ){
			$options['order'] = $pk.' desc';
		}elseif($order){
			$options['order'] = $order;
		}

		$where  			= empty($where) ?  array() : $where;
		$options['where']   = $where;
		$options      		= array_merge( (array)$OPT->getValue($model), $options );
		$total        		= $model->where($options['where'])->count();
		$defLimit			= intval($limit) > 0 ? intval($limit) : C('LIST_ROWS');
		$listLimit 			= $defLimit > 0 ? $defLimit : 10;
		$remainder			= intval($total-$listLimit*$page);
		//是否分页
		if ($ispage == true){
			$page 				= new \Think\Page($total, $listLimit, $REQUEST);
			if($total>$listLimit){
				$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
			}
			$page->rollPage		= 20;
			$p 					= trim($page->show());
			$options['limit'] 	= $page->firstRow.','.$page->listRows;
		}

		$model->setProperty('options',$options);
		$this->_remainder	= $remainder >= 0 ? intval($remainder) : 0;
		$this->_total		= intval($total);
		$this->_page		= !empty($p)? $p : '';
		if ($ispage == true){
			return $model->field($field)->select();
		}
		return $model->field($field)->limit($listLimit)->page($page)->select();
	}
	//通用一条数据集获取方法
	protected function getOne($model,$where=array(),$field=true){
		$model  	= is_string($model) ? M($model) : $model;
		return $model->field($field)->where($where)->find();
	}
	//定义关联查询表以及字段
	protected function getRelationTab($RelationTab){
		$tables	  		= array();
		$fields 		= '';
		if (!empty($RelationTab)){
			$prefix   		= C('DB_PREFIX');
			foreach ($RelationTab as $key=>$val){
				$Rtables	= $key;
				$Ron		= trim($val['Ron']);
				$Rfield		= $val['Rfield'];
				$Ralias		= $val['Ralias'];
				if (empty($Rtables) || empty($Ron) || empty($Ralias)){
					continue;
				}else{
					$tables[] 	= $prefix.$Rtables.' '.$Ron;
					if ($Rfield === true || empty($Rfield)){
						$fields				.= get_fields_string(M($Rtables)->getDbFields(),$Ralias).',';
					}elseif (is_string($Rfield)){
						$fields				.= get_fields_string(implode(',', $Rfield),$Ralias).',';
					}elseif (is_array($Rfield)){
						$fields				.= get_fields_string($Rfield,$Ralias).',';
					}
				}
			}
		}
		return array('tables'=>$tables,'fields'=>$fields);
	}
}
?>