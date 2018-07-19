<?php
namespace Admin\Controller;
/**
 * 优惠券记录控制器
 */
class CouponRecordsController extends AdminController {

	public function index(){
		$limit						= 20;

		//获取数据
		$MainTab					= 'CouponRecords';
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
		$RelationTab				= array(
			'user'=>array('Ralias'=>'u','Ron'=>'u ON u.id=main.userId','Rfield'=>array('nickname')),
			'coupon'=>array('Ralias'=>'c','Ron'=>'c ON c.id=main.couponId','Rfield'=>array('name')),
		);
		$RelationTab				= $this->getRelationTab($RelationTab);
		$tables	  					= $RelationTab['tables'];
		$RelationFields				= $RelationTab['fields'];
		$model						= !empty($tables) ? $MainModel->join ( $tables ,'LEFT') : $MainModel;

		//检索条件
		$map 						= array();

		//关键词检索
		$keyword 					= I('find_keyword','');
		if(!empty($keyword)){
			$map['_complex'] 		= array(
				'phone' => array('like', '%'.$keyword.'%'),
				'_logic' 	=> 'OR',
			);
		}
		
		//状态检索
		$status 					= I('get.find_status','');
		if($status != ''){
			$map['isUsed'] 			= intval($status);
		}

		//排序
		$order						= $MainAlias.'.id desc';

		//检索字段
		$fields						= (empty($MainField) ? $this->get_fields_string($MainModel->getDbFields(),$MainAlias).',' : $this->get_fields_string($MainField,$MainAlias).',') . $RelationFields;
		$fields						= trim($fields,',');

		//列表数据
		$isUsed						= array('未使用','已使用');
		$list 						= $this->getLists($model,$map,$order,$fields,1,$limit,true);
		
		if (!empty($list)){
			foreach ($list as $k=>$v){
				//数据格式化
				//$list[$k]['isUsed']			= $isUsed[$v['isUsed']];
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
		$this->NavTitle = '优惠券记录管理';
		$this->assign('SmallNav', 			array('优惠券记录管理','优惠券记录列表'));
		
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
			$info = D('CouponRecords')->field(true)->where(array('id'=>$id))->find();
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
			$Models 		= D('CouponRecords');
			//数据整理
			//.......
			//数据整理
			$res 			= $Models->update();
			if(false !== $res){
				S('DB_CONFIG_DATA',null);
				action_log('couponRecords',$res['id'],UID);
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
		if(M('CouponRecords')->where($map)->delete()){
			S('DB_CONFIG_DATA',null);
			//记录行为
			action_log('couponRecords',$ids,UID);
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
		);
		
		return $FormData[$index];
	}
}
?>