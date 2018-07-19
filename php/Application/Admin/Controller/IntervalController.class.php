<?php
namespace Admin\Controller;
/**
 * 控制器
 */
class IntervalController extends AdminController {

	public function index(){
		$limit						= 20;

		//获取数据
		$MainTab					= 'interval';
		$MainAlias					= 'main';
		$MainField					= array();

		//主表模型
		$MainModel 					= D($MainTab)->alias($MainAlias);

		/*
		 * 灵活定义关联查询
		 * Ralias 	关联表别名
		 * Ron    	关联条件
		 * Rfield	关联查询字段，
		 * */
		$RelationTab				= $this->getRelationTab($RelationTab);
		$tables	  					= $RelationTab['tables'];
		$RelationFields				= $RelationTab['fields'];
		$model						= !empty($tables) ? $MainModel->join ( $tables ,'LEFT') : $MainModel;

		//检索条件
		$map 						= array();
		
		/* $type						= intval(I('get.type',0));
		if ($type >0){
			$map['type']			= $type;
		} */

		//时间区间检索
		$search_time				= time_between('create_time',$MainAlias,'endTime');
		//关键词检索
		$keyword 					= I('find_keyword','');
		if(!empty($keyword)){
			$map['_complex'] 		= array(
				'parkName' => array('like', '%'.$keyword.'%'),
				'_logic' 	=> 'OR',
			);
		}

		$map						= array_merge($map,$search_time);
		//排序
		$order						= $MainAlias.'.type asc,'.$MainAlias.'.lower asc';

		//检索字段
		$fields						= (empty($MainField) ? $this->get_fields_string($MainModel->getDbFields(),$MainAlias).',' : $this->get_fields_string($MainField,$MainAlias).',') . $RelationFields;
		$fields						= trim($fields,',');

		//列表数据
		$list 						= $this->getLists($model,$map,$order,$fields,1,$limit,true);
		
		
		if (!empty($list)){
			foreach ($list as $k=>$v){
				//数据格式化
				
			}
		}
		$this->assign('_list', $list);
		//操作菜单,可以根据需要固定$menuid,$menuid为Menu表中的ID
		$menuid								= $this->menuid;
		$SonMenu							= $this->getSonMenu($menuid);
		$this->assign('ListTopNav', 		!empty($SonMenu['TOPMENU']) ? $SonMenu['TOPMENU'] : array());
		$this->assign('ListRightNav', 		!empty($SonMenu['RIGHTMENU']) ? $SonMenu['RIGHTMENU'] : array());
		
		//代码扩展
		$this->extends_param				.= $this->extends_param;
		//.........
		//代码扩展
		$this->NavTitle = '区间管理';
		$this->assign('SmallNav', 			array('区间管理','区间列表'));
		
		//记录当前列表页的cookie
		if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf')) Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->display();
	}
	
	/**
	 * 新增编辑配置
	 */
	public function addedit($id = 0){
		//数据提交
		if (IS_POST) $this->update();
		
		$info = array();
		if(!empty($id)){
			$info = D('Interval')->field(true)->where(array('id'=>$id))->find();
			$this->NavTitle 	= '编辑';
		}else{
			$this->NavTitle 	= '新增';
		}
		$this->assign('info', $info);
		//表单数据
		$FormData						= $this->CustomerForm(0); 
		$this->assign('FormData',       $FormData);
		$this->display();
	}

	//提交表单
	protected function update(){
		if(IS_POST){
			$Models 		= D('Interval');
			//数据整理
			//.......
			//数据整理
			$res 			= $Models->update();
			if(false !== $res){
				S('DB_CONFIG_DATA',null);
				action_log('interval',$res['id'],UID);
				//记录行为
				$this->success($res['ac']>0 ? '更新成功' : '新增成功', Cookie('__forward__'));
			}
			else
			{
				$error = $Models->getError();
				$this->error(empty($error) ? '未知错误！' : $error);
			}
		}
		$this->error('非法提交！');
	}

	/**
	 * 删除
	 */
	public function del(){
		$ids			= I('request.ids');
		if ( empty($ids) ) { $this->error('请选择要操作的数据!');}
		$ids 			= is_array($ids) ? $ids : array(intval($ids));
		$ids			= array_unique($ids);
		$map 			= array('id' => array('in', $ids) );
		if(M('Interval')->where($map)->delete()){
			S('DB_CONFIG_DATA',null);
			//记录行为
			action_log('interval',$ids,UID);
			$this->success('删除成功',Cookie('__forward__'));
		} else {
			$this->error('删除失败！');
		}
	}

	/*
	 * fieldName	字段名称
	 * fieldValue	字段值
	 * fieldType	字段类型[
	 * 				text		:文本
	 * 				password	:密码
	 * 				checkbox	:复选
	 * 				radio		:单选
	 * 				hidden		:隐藏域
	 * 				select		:下拉框
	 * 				textarea	:多行文本
	 * 				editor		:编辑器
	 * 				image		:单图上传
	 * 				images		:多图上传
	 * 				maps		:地图
	 * 				address		:地址级联
	 * isMust		是否必填
	 * fieldData	字段数据[字段类型为radio,select,checkbox时的列表数据]
	 * Attr			标签属性[常见有:id,class,placeholder,style....]
	 * */
	protected function CustomerForm($index=0){
		$FormData[0] = array(
			array('fieldName'=>'类型','fieldValue'=>'type','fieldType'=>'select','isMust'=>1,'fieldData'=>array(0=>'--请选择--',1=>'现金',2=>'积分'),'attrExtend'=>'placeholder=""'),
			array('fieldName'=>'起始','fieldValue'=>'lower','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入起始数目"'),
			array('fieldName'=>'截止','fieldValue'=>'higher','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入截止数目"'),
			array('fieldName'=>'排序','fieldValue'=>'sort','fieldType'=>'text','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入排序"'),
			array('fieldName'=>'隐藏域','fieldValue'=>array('id'),'fieldType'=>'hidden','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder=""'),
		);
		
		return $FormData[$index];
	}
}
?>