<?php
namespace Admin\Controller;
/**
 * 后台分类管理控制器
 */
class CategoryController extends AdminController {
	/**
	 * 分类管理列表
	 */
	public function index(){
		$map				= array();
		$map['status']		= array('egt', 0);
		$keyword			= I('get.find_keyword','');
		$create_time_s		= I('get.create_time_s','');
		$create_time_e		= I('get.create_time_e','');
		if(!empty($keyword)){
			$where['name']  		= array('like', '%'.$keyword.'%');
			$where['_logic'] 		= 'or';
			$map['_complex'] 		= $where;
		}

		$create_time				= time_between('create_time');
		$map						= array_merge($map,$create_time);
		$list 						= D('Category')->getTree(0,'id,name,sort,pid,status',$map);

		if (!empty($list)){
			$status					= array(1=>'启用',2=>'禁用');
			foreach ($list as $k=>$v){
				//数据格式化
				$list[$k]['create_time']	= date('Y-m-d H:i',$v['create_time']);
				$list[$k]['status_text']	= $status[$v['status']];
			}
		}
		$this->assign('tree', $list);
		C('_SYS_GET_CATEGORY_TREE_', true); //标记系统获取分类树模板

		//操作菜单
		$SonMenu							= $this->getSonMenu($this->menuid);
		$this->assign('ListTopNav', 		!empty($SonMenu['TOPMENU']) ? $SonMenu['TOPMENU'] : array());
		$this->assign('ListRightNav', 		!empty($SonMenu['RIGHTMENU']) ? $SonMenu['RIGHTMENU'] : array());

		//分类分组
		$this->NavTitle = '分类管理';
		$this->assign('SmallNav', 			array('分类管理','分类列表'));
		if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf')) Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->display();
	}

	/**
	 * 显示分类树，仅支持内部调
	 * @param  array $tree 分类树
	 */
	public function tree($tree = null){
		C('_SYS_GET_CATEGORY_TREE_') || $this->_empty();
		if (!empty($tree)){
			$status					= array(1=>'启用',2=>'禁用');
			foreach ($tree as $k=>$v){
				//数据格式化
				$tree[$k]['create_time']	= date('Y-m-d H:i',$v['create_time']);
				$tree[$k]['status_text']	= $status[$v['status']];
			}
		}
		$this->assign('tree', $tree);
		$this->display('tree');
	}
	public function gettree(){
		$tree 		= D('Category')->getTree(0,'id,name,sort,pid,status');
		$this->assign('tree', $tree);
		C('_SYS_GET_CATEGORY_TREE_', true); //标记系统获取分类树模板
		echo $this->fetch();exit();
	}
	/**
	 * 新增分类
	 */
	public function add(){
		if (IS_POST) $this->update();
		$FormData						= $this->CustomerForm(0);
		$this->assign('FormData',       $FormData);

		$info['pid']					= intval(I('get.pid'));
		$info['status']					= 2;
		$info['isrecommend']			= 2;
		$this->assign('info',       	$info);
		$this->NavTitle 				= '新增菜单';
		$this->display('addedit');
	}
	/**
	 * 编辑分类
	 */
	public function edit($id = 0){
		if (IS_POST) $this->update();
		/* 获取数据 */
		$info 			= M('Category')->field(true)->find($id);
		if(false === $info){
			$this->error('获取后台分类信息错误',Cookie('__forward__'));
		}
		//表单数据
		$FormData						= $this->CustomerForm(0);
		$this->assign('FormData',       $FormData);
		$this->assign('info',       	$info);
		$this->NavTitle 				= '编辑菜单';
		$this->display('addedit');
	}
	/**
	 * 删除一个分类
	 */
	public function del(){
		$ids			= I('request.ids');
		if ( empty($ids) ) { $this->error('请选择要操作的数据!');}
		$ids 			= is_array($ids) ? $ids : array(intval($ids));
		$ids			= array_unique($ids);
		foreach ($ids as $v){
			$child = M('Category')->where(array('pid'=>$v))->field('id')->select();
			if (!empty($child)){
				foreach ($child as $vv){
					if (!in_array($vv['id'], $ids)){
						$catname	= M('Category')->where(array('id'=>$v))->getField('name');
						$this->error('请先删除【'.$catname.'】分类下的所有子分类'.$vv['id']);
					}
				}
			}
		}
		$ids			= implode(',', $ids);
		$map 			= array('id' => array('in', $ids) );
		if(M('Category')->where($map)->delete()){
			//记录行为
			action_log('Category/del',$ids,UID);
			//数据返回
			$this->success('删除成功',Cookie('__forward__'));
		} else {
			$this->error('删除失败！');
		}
	}
	//提交表单
	protected function update(){
		if(IS_POST){
			$Models 		= D('Category');
			//数据整理
			//.......
			//数据整理
			$res 			= $Models->update();
			if(false !== $res){
				//记录行为
				action_log('Category',$data['id'],UID);
				//数据返回
				$this->success($res['ac']>0 ? '更新成功' : '新增成功', Cookie('__forward__'));
			}
			else{
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
		$menus 				= M('Category')->field(array('id','name as title','pid'))->where(array('pid'=>0))->select();
		$menus 				= D('Common/Tree')->toFormatTree($menus);
		$pidData			= array();
		if (!empty($menus)){
			foreach ($menus as $k=>$v){
				$pidData[$k]	= array('id'=>$v['id'],'name'=>$v['title_show']);
			}
		}
		$pidData 			= array_merge(array(0=>array('id'=>0,'name'=>'顶级分类')), $pidData);
		
		$FormData[0] = array(
		array('fieldName'=>'分类名称','fieldValue'=>'name','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="用于后台显示的菜单名称"'),
		//array('fieldName'=>'上级分类','fieldValue'=>'pid','fieldType'=>'select','isMust'=>0,'fieldData'=>$pidData,'attrExtend'=>'placeholder=""'),
		//array('fieldName'=>'分类LOGO','fieldValue'=>'icon','fieldType'=>'image','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'data-table="category" data-field="icon" data-size=""'),
		array('fieldName'=>'排序','fieldValue'=>'sort','fieldType'=>'text','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder="用于分组显示的顺序"'),
		array('fieldName'=>'状态','fieldValue'=>'status','fieldType'=>'radio','isMust'=>0,'fieldData'=>array(1=>'启用',2=>'禁用'),'attrExtend'=>'placeholder=""'),
		//array('fieldName'=>'首页推荐','fieldValue'=>'isrecommend','fieldType'=>'radio','isMust'=>0,'fieldData'=>array(1=>'推荐',2=>'不推荐'),'attrExtend'=>'placeholder=""'),
		array('fieldName'=>'隐藏域','fieldValue'=>array('id'),'fieldType'=>'hidden','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder=""'),
		array('fieldName'=>'隐藏域','fieldValue'=>array('uid'),'fieldType'=>'hidden','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder=""'),
		);
		return $FormData[$index];
	}
}
?>