<?php
namespace Admin\Controller;
/**
 * 后台配置控制器
 */
class FeedBackController extends AdminController {
	/**
	 * 修改成自己的
	 * @author xxx
	 */
	public function index(){
		$limit						= 20;

		//获取数据
		$MainTab					= 'feedback';
		$MainAlias					= 'main';
		$MainField					= array();

		//主表模型
		$MainModel 					= M($MainTab)->alias($MainAlias);

		/*
		 * 灵活定义关联查询
		 * Ralias 	关联表别名
		 * Ron    	关联条件
		 * Rfield	关联查询字段，
		 * */
		$RelationTab				= array(
			'user'=>array('Ralias'=>'u','Ron'=>'u ON u.id=main.uid','Rfield'=>array('phone')),
		);
		$RelationTab				= $this->getRelationTab($RelationTab);
		$tables	  					= $RelationTab['tables'];
		$RelationFields				= $RelationTab['fields'];
		$model						= !empty($tables) ? $MainModel->join ( $tables ,'LEFT') : $MainModel;

		//检索条件
		$map 						= array();

		//时间区间检索
		$create_time				= time_between('create_time',$MainAlias);
		
		//关键词检索
		$keyword 					= I('find_keyword','');
		if(!empty($keyword)){
			$map['_complex'] 		= array(
				'title' => array('like', '%'.$keyword.'%'),
				'_logic' 	=> 'OR',
			);
		}
		
		//状态检索
		$status 				= intval(I('get.find_status',0));
		if(!empty($status) && $status > 0){
			$map['status'] 		= $status;
		}

		$map						= array_merge($map,$create_time);
		//排序
		$order						= $MainAlias.'.create_time desc';

		//检索字段
		$fields						= (empty($MainField) ? $this->get_fields_string($MainModel->getDbFields(),$MainAlias).',' : $this->get_fields_string($MainField,$MainAlias).',') . $RelationFields;
		$fields						= trim($fields,',');

		//列表数据
		$list 						= $this->getLists($model,$map,$order,$fields,1,$limit,true);
		if (!empty($list)){
			foreach ($list as $k=>$v){
				//数据格式化
				$list[$k]['create_time']	= $v['create_time'] > 0 ? date('Y-m-d H:i:s',$v['create_time']) : '--';
			}
		}
		$this->assign('_list', $list);

		//操作菜单,可以根据需要固定$menuid,$menuid为Menu表中的ID
		$menuid								= $this->menuid;
		$SonMenu							= $this->getSonMenu($menuid);
		$this->assign('ListTopNav', 		!empty($SonMenu['TOPMENU']) ? $SonMenu['TOPMENU'] : array());
		$this->assign('ListRightNav', 		!empty($SonMenu['RIGHTMENU']) ? $SonMenu['RIGHTMENU'] : array());

		//代码扩展
		//.........
		//代码扩展

		$this->NavTitle = '意见反馈管理';
		//记录当前列表页的cookie
		if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf')) Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->display();
	}

	/**
	 * 删除数据
	 */
	public function del() {
		$ids = I('request.ids');
		if (empty($ids)) {
			$this->error('请选择要操作的数据!');
		}
		$ids = is_array($ids) ? $ids : array(intval($ids));
		$ids = array_unique($ids);
		$map = array('id' => array('in', $ids));
		if (M('Feedback')->where($map)->delete()) {
			//记录行为
			action_log('feedback', $id, UID);
			//数据返回
			$this->success('删除成功', Cookie('__forward__'));
		} else {
			$this->error('删除失败！');
		}
	}
}
?>