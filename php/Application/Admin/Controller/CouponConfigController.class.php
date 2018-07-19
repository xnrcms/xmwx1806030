<?php
namespace Admin\Controller;
/**
 * 后台配置控制器
 */
class CouponConfigController extends AdminController {
	/**
	 * @author xiaoQ
	 */
	public function index(){
		$limit						= 20;

		//获取数据
		$MainTab					= 'CouponConfig';
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
		//'member'=>array('Ralias'=>'me','Ron'=>'me ON me.uid=main.uid','Rfield'=>array('uid as uuid','nickname')),
		);
		$RelationTab				= $this->getRelationTab($RelationTab);
		$tables	  					= $RelationTab['tables'];
		$RelationFields				= $RelationTab['fields'];
		$model						= !empty($tables) ? $MainModel->join ( $tables ,'LEFT') : $MainModel;

		//检索条件
		$map 						= array();
		$cate_id					= intval(I('get.cate_id',0));
		$cid						= array();
		if ($cate_id >0){
			$cid 					= D('Category')->getChildrenId($cate_id);
			$map['category_id']		= array(array('in',$cid),array('gt',0));
		}

		//时间区间检索
		$search_time				= time_between('update_time',$MainAlias);
		//关键词检索
		$keyword 					= I('find_keyword','');
		if(!empty($keyword)){
			$map['_complex'] 		= array(
				'goodsname' => array('like', '%'.$keyword.'%'),
				'_logic' 	=> 'OR',
			);
		}

		//状态检索
		$status 				= intval(I('get.find_status',0));
		if(!empty($status) && $status > 0){
			$map['status'] 		= $status;
		}

		$map						= array_merge($map,$search_time);
		//排序
		$order						= $MainAlias.'.id desc';

		//检索字段
		$fields						= (empty($MainField) ? $this->get_fields_string($MainModel->getDbFields(),$MainAlias).',' : $this->get_fields_string($MainField,$MainAlias).',') . $RelationFields;
		$fields						= trim($fields,',');

		//列表数据
		$status						= array('删除','上架','下架');
		$list 						= $this->getLists($model,$map,$order,$fields,1,$limit,true);
		
		
		
		if (!empty($list)){
			foreach ($list as $k=>$v){
				//数据格式化
				$list[$k]['status']			= $status[$v['status']];
				$list[$k]['create_time']	= $v['create_time'] > 0 ? date('Y-m-d H:i:s',$v['create_time']) : '--';
				$list[$k]['update_time']	= $v['update_time'] > 0 ? date('Y-m-d H:i:s',$v['update_time']) : '--';
			}
		}
		$this->assign('_list', $list);
		//操作菜单,可以根据需要固定$menuid,$menuid为Menu表中的ID
		$menuid								= $this->menuid;
		$SonMenu							= $this->getSonMenu($menuid);
		$this->assign('ListTopNav', 		!empty($SonMenu['TOPMENU']) ? $SonMenu['TOPMENU'] : array());
		$this->assign('ListRightNav', 		!empty($SonMenu['RIGHTMENU']) ? $SonMenu['RIGHTMENU'] : array());

		//代码扩展
		$this->extends_param				.= $this->extends_param .'&cate_id='.I('get.cate_id'); 
		//.........
		//代码扩展

		$this->NavTitle = '配置管理';
		$ParentCatName						= D('Category')->getParentName(3,1);
		if (empty($ParentCatName)){
			$this->assign('SmallNav', 			array('优惠券配置管理','优惠券配置列表'));
		}else{
			$cname[]	= '优惠券配置管理';
			foreach ($ParentCatName as $v){
				$cname[]	= $v['name'];
			}
			$this->assign('SmallNav', 			$cname);
		}
		//记录当前列表页的cookie
		if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf')) Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->display();
	}
	
	/**
	 * 数据
	 */
	public function addedit($id = 0){
		//数据提交
		if (IS_POST) $this->update();
	
		$info 							= array();
		if($id){
			//页面数据
			$info 			= D('CouponConfig')->field(true)->find($id);
			if(false === $info){
				$this->error('获取信息错误');
			}
			$this->NavTitle = '编辑';
		}else{
			//页面数据
			$info['type']				= 0;
			$this->NavTitle 			= '新增';
		}
		$this->assign('info',$info);
	
		//表单数据
		$FormData						= $this->CustomerForm(0);
		$this->assign('FormData',       $FormData);
		
		$this->display();
	}

	/**
	 * 删除数据
	 */
	public function del(){
		$ids			= I('request.ids');
		if ( empty($ids) ) { $this->error('请选择要操作的数据!');}
		$ids 			= is_array($ids) ? $ids : array(intval($ids));
		$ids			= array_unique($ids);
		$map 			= array('id' => array('in', $ids) );
		if(D('CouponConfig')->where($map)->delete()){
			//记录行为
			action_log('config',$ids,UID);
			//数据返回
			$this->success('删除成功',Cookie('__forward__'));
		} else {
			$this->error('删除失败！');
		}
	}

	//提交表单
	protected function update(){
		if(IS_POST){
			$Models 		= D('CouponConfig');
			//数据整理
			//.......
			//数据整理
			$res 			= $Models->update();
			if(false !== $res){
				//记录行为
				action_log('article',$res['id'],UID);
				//数据返回
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

	/*
	 * fieldName	字段名称
	 * fieldValue	字段值
	 * fieldType	字段类型[
	 * 				text		:文本
	 * 				password	:密码
	 * 				checkbox	:复选
	 * 				radio		:单选
	 * 				select		:下拉框
	 * 				textarea	:多行文本
	 * 				editor		:编辑器
	 * 				image		:单图上传
	 * 				images		:多图上传
	 * 				maps		:地图
	 * 				city		:城市选择
	 * 				datetime	:日期格式
	 * 				hidden		:隐藏域
	 * isMust		是否必填
	 * fieldData	字段数据[字段类型为radio,select,checkbox时的列表数据]
	 * Attr			标签属性[常见有:id,class,placeholder,style....]
	 * */
	protected function CustomerForm($index=0){
		
		$FormData[0] = array(
			array('fieldName'=>'摇券次数','fieldValue'=>'shakenum','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入摇券次数"'),
			array('fieldName'=>'概率','fieldValue'=>'probability','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入概率"'),
			array('fieldName'=>'隐藏域','fieldValue'=>array('id'),'fieldType'=>'hidden','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder=""'),
		);
		return $FormData[$index];
	}
}
?>