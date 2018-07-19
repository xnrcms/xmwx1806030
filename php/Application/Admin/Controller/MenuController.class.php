<?php
namespace Admin\Controller;
/**
 * 后台配置控制器
 */
class MenuController extends AdminController {
	/**
	 * 后台菜单首页
	 * @return none
	 */
	public function index(){
		$pid  	= I('get.pid',0);
		if(!empty($pid) && $pid > 0){
			$data 			= M('Menu')->where(array('id'=>$pid))->field(true)->find();
			$this->assign('data',$data);
		}
		$keyword    = trim(I('get.find_keyword'));
		$all_menu   = M('Menu')->getField('id,title');
		$map['pid'] = $pid;
		if(!empty($keyword)) $map['title'] = array('like',"%{$keyword}%");

		$list 		= M("Menu")->where($map)->field(true)->order('sort asc,id asc')->select();
		int_to_string($list,array('hide'=>array(1=>'是',0=>'否'),'is_dev'=>array(1=>'是',0=>'否')));
		if($list) {
			foreach($list as &$key){
				if($key['pid']){
					$key['up_title'] = $all_menu[$key['pid']];
				}
			}
		}
		$this->assign('_list',$list);

		//操作菜单
		$SonMenu							= $this->getSonMenu(2);
		$this->assign('ListTopNav', 		!empty($SonMenu['TOPMENU']) ? $SonMenu['TOPMENU'] : array());
		$this->assign('ListRightNav', 		!empty($SonMenu['RIGHTMENU']) ? $SonMenu['RIGHTMENU'] : array());

		//记录当前列表页的cookie
		if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf')) Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->NavTitle 					= '菜单列表';
		$this->assign('SmallNav', 			array('菜单管理',$this->NavTitle));
		$this->extends_param				= '&pid='.$pid;
		$this->display();
	}

	/**
	 * 新增菜单
	 */
	public function add(){
		if (IS_POST) $this->update();
		$FormData						= $this->CustomerForm(0);
		$this->assign('FormData',       $FormData);

		$info['pid']					= intval(I('get.pid'));
		$this->assign('info',       	$info);
		$this->NavTitle 				= '新增菜单';
		$this->display('addedit');
	}

	/**
	 * 编辑配置
	 */
	public function edit($id = 0){
		if (IS_POST) $this->update();
		/* 获取数据 */
		$info 			= M('Menu')->field(true)->find($id);
		if(false === $info){
			$this->error('获取后台菜单信息错误',Cookie('__forward__'));
		}
		
		//表单数据
		$FormData						= $this->CustomerForm(0);
		$this->assign('FormData',       $FormData);
		
		$this->assign('info',       	$info);
		$this->NavTitle 				= '编辑菜单';
		$this->display('addedit');
	}

	//提交表单
	protected function update()
	{
		if(IS_POST){
			$Models 		= D('Menu');
			//数据整理
			//.......
			//数据整理
			$res 			= $Models->update();
			if(false !== $res){
				$RuleModel			= D('AuthRule');
				$menuData['name']   = MODULE_NAME . '/' . $res['url'];
				$menuData['title']  = $res['title'];
				$menuData['module'] = 'admin';
				$menuData['menuid']	= $res['id'];
				$ruleid				= $RuleModel->where(array('menuid'=>$res['id']))->getField('id');
				$menuData['id']		= empty($ruleid) ? 0 : $ruleid;
				$menuData['type']	= $res['pid'] > 0 ? $RuleModel::RULE_URL : $RuleModel::RULE_MAIN;
				$menuData['status']  = 1;
				D('AuthRule')->update($menuData);
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
	 * 删除后台菜单
	 */
	public function del(){
		$ids			= I('request.ids');
		if ( empty($ids) ) { $this->error('请选择要操作的数据!');}
		$ids 			= is_array($ids) ? $ids : array(intval($ids));
		$ids			= array_unique($ids);
		$map 			= array('id' => array('in', $ids) );
		if(M('Menu')->where($map)->delete()){
			S('DB_CONFIG_DATA',null);
			//记录行为
			action_log('Menu', $id, UID);
			$this->success('删除成功', Cookie('__forward__'));
		} else {
			$this->error('删除失败！');
		}
	}

	public function toogleHide($id,$value = 1){
		$this->editRow('Menu', array('hide'=>$value), array('id'=>$id),array('url'=>Cookie('__forward__')));
	}

	public function toogleDev($id,$value = 1){
		$this->editRow('Menu', array('is_dev'=>$value), array('id'=>$id),array('url'=>Cookie('__forward__')));
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
		$menus 				= M('Menu')->field(array('id','title','pid'))->select();
		$menus 				= D('Common/Tree')->toFormatTree($menus);
		$pidData			= array();
		if (!empty($menus)){
			foreach ($menus as $k=>$v){
				$pidData[$k]	= array('id'=>$v['id'],'name'=>$v['title_show']);
			}
		}
		$pidData 			= array_merge(array(0=>array('id'=>0,'name'=>'顶级菜单')), $pidData);
		$posttypeData		= C('MENU_POSTTYPE');
		$FormData[0] = array(
		array('fieldName'=>'名称','fieldValue'=>'title','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="用于后台显示的菜单名称"'),
		array('fieldName'=>'链接','fieldValue'=>'url','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="U函数解析的URL或者外链"'),
		array('fieldName'=>'上级菜单','fieldValue'=>'pid','fieldType'=>'select','isMust'=>0,'fieldData'=>$pidData,'attrExtend'=>'placeholder=""'),
		array('fieldName'=>'分组','fieldValue'=>'group','fieldType'=>'text','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder="用于左侧分组二级菜单"'),
		array('fieldName'=>'排序','fieldValue'=>'sort','fieldType'=>'text','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder="用于分组显示的顺序"'),
		array('fieldName'=>'是否禁用','fieldValue'=>'status','fieldType'=>'radio','isMust'=>0,'fieldData'=>array('1'=>'否','0'=>'是'),'attrExtend'=>'placeholder=""'),
		array('fieldName'=>'是否隐藏','fieldValue'=>'hide','fieldType'=>'radio','isMust'=>0,'fieldData'=>array('0'=>'否','1'=>'是'),'attrExtend'=>'placeholder=""'),
		array('fieldName'=>'开发可见','fieldValue'=>'is_dev','fieldType'=>'radio','isMust'=>0,'fieldData'=>array('0'=>'否','1'=>'是'),'attrExtend'=>'placeholder=""'),
		array('fieldName'=>'操作类型','fieldValue'=>'posttype','fieldType'=>'radio','isMust'=>0,'fieldData'=>$posttypeData,'attrExtend'=>'placeholder=""'),
		array('fieldName'=>'备注说明','fieldValue'=>'tip','fieldType'=>'text','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder=""'),
		array('fieldName'=>'隐藏域','fieldValue'=>array('id'),'fieldType'=>'hidden','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder=""'),
		);
		return $FormData[$index];
	}
}
?>