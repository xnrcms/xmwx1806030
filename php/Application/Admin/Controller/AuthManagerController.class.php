<?php
namespace Admin\Controller;
use Admin\Model\AuthRuleModel;
use Admin\Model\AuthGroupModel;
/**
 * 权限管理控制器
 * Class AuthManagerController
 */
class AuthManagerController extends AdminController{

	/**
	 * 权限管理首页
	 */
	public function index(){
		$list 				= $this->lists('AuthGroup',array('module'=>'admin'),'id asc');
		$status				= array(1=>'正常',0=>'禁用');
		if (!empty($list)){
			foreach ($list as $k=>$v){
				$list[$k]['update_time']	= date('Y-m-d H:s',$v['update_time']);
				$list[$k]['status_text']	= $status[$v['status']];
			}
		}
		$this->assign( '_list', $list );
		$this->assign( '_use_tip', true );

		//操作菜单
		$SonMenu							= $this->getSonMenu($this->menuid);
		$this->assign('ListTopNav', 		!empty($SonMenu['TOPMENU']) ? $SonMenu['TOPMENU'] : array());
		$this->assign('ListRightNav', 		!empty($SonMenu['RIGHTMENU']) ? $SonMenu['RIGHTMENU'] : array());

		// 记录当前列表页的cookie
		Cookie('__forward__',$_SERVER['REQUEST_URI']);

		$this->NavTitle = '权限列表';
		$this->assign('SmallNav', 			array('权限管理',$this->NavTitle));
		$this->display();
	}

	/**
	 * 创建管理员用户组
	 */
	public function add(){
		if (IS_POST) $this->update();
		$FormData						= $this->CustomerForm(0);
		$this->assign('FormData',       $FormData);

		if ( empty($this->auth_group) ) {
			$this->assign('auth_group',array('title'=>null,'id'=>null,'description'=>null,'rules'=>null,));//排除notice信息
		}
		$this->NavTitle 				= '新增用户组';
		$this->display('addedit');
	}

	/**
	 * 编辑管理员用户组
	 */
	public function edit(){
		if (IS_POST) $this->update();
		$FormData						= $this->CustomerForm(0);
		$this->assign('FormData',       $FormData);

		$id								= intval(I('get.id'));
		$map							= array(array('id'=>$id,'module'=>'admin','type'=>AuthGroupModel::TYPE_ADMIN));
		$info 							= M('AuthGroup')->where($map)->find();
		empty($info) && $this->error('数据不存在');
		$this->assign('info',$info);

		$this->NavTitle 				= '编辑用户组';
		$this->display('addedit');
	}
	//删除
	public function del(){
		$Models 		= M('AuthGroup');
		$ids			= I('request.ids');
		if (is_numeric($ids))
		{
			$ids 		= array($ids);
		}
		if (empty($ids))
		{
			$this->error('请选择要操作的数据');
		}
		if(is_array($ids))
		{
			$map['id']	= array('in',$ids);
			$Models->where($map)->delete();
			$this->success("删除成功！", Cookie('__forward__'));
		}
		else
		{
			$this->error('非法提交！');
		}
	}

	/**
	 * 访问授权页面
	 */
	public function accessVisit(){
		if (IS_POST) $this->setAccessVisit();
		$this->updateRules();
		//所有角色组
		$map			= array('status'=>array('egt','0'),'module'=>'admin','type'=>AuthGroupModel::TYPE_ADMIN);
		$auth_group 	= M('AuthGroup')->where( $map )->getfield('id,id,title,rules');
		$node_list   	= $this->returnNodes();
		$map         	= array('module'=>'admin','type'=>AuthRuleModel::RULE_MAIN,'status'=>1);
		$main_rules  	= M('AuthRule')->where($map)->getField('name,id');

		$map         	= array('module'=>'admin','type'=>AuthRuleModel::RULE_URL,'status'=>1);
		$child_rules 	= M('AuthRule')->where($map)->getField('name,id');

		$this->assign('main_rules', $main_rules);
		$this->assign('auth_rules', $child_rules);
		$this->assign('node_list',  $node_list);
		$this->assign('auth_group', $auth_group);
		$this->assign('this_group', $auth_group[intval(I('get.id'))]);
		$this->NavTitle = '访问授权';
		$this->display();
	}
	/**
	 * 用户权限设置
	 */
	public function accessMember(){
		if (IS_POST) $this->setAccessMember();
		$uid					= I('request.uid',0,'intval');
		$hashid					= I('request.hashid','');
		if (!$this->checkId($uid,$hashid)) $this->error('非法参数！');

		$FormData						= $this->CustomerForm(1);
		$this->assign('FormData',       $FormData);
		//所有角色组
		$map			= array('status'=>array('egt','0'),'module'=>'admin','type'=>AuthGroupModel::TYPE_ADMIN);
		$auth_group 	= M('AuthGroup')->where( $map )->getfield('id,id,title,rules');
		$node_list   	= $this->returnNodes();

		$map         	= array('module'=>'admin','type'=>AuthRuleModel::RULE_MAIN,'status'=>1);
		$main_rules  	= M('AuthRule')->where($map)->getField('name,id');

		$map         	= array('module'=>'admin','type'=>AuthRuleModel::RULE_URL,'status'=>1);
		$child_rules 	= M('AuthRule')->where($map)->getField('name,id');

		$rules			= M('member')->where(array('uid'=>$uid))->getField('rules');
		$this->assign('rules', $rules);

		$auth_group_id		= M('auth_group_access')->where(array('uid'=>$uid))->field('group_id')->select();
		if (!empty($auth_group_id)){
			foreach ($auth_group_id as $v){
				$group_id[$v['group_id']]	= $v['group_id'];
			}
		}
		$info['groupid']	= !empty($group_id) ? implode(',', $group_id) : '';
		$info['id']			= $uid;
		$info['hashid']		= $hashid;
		$this->assign('info',       $info);

		$this->assign('main_rules', $main_rules);
		$this->assign('auth_rules', $child_rules);
		$this->assign('node_list',  $node_list);
		$this->assign('auth_group', $auth_group);
		$this->NavTitle 			= '用户授权';
		$this->display();
	}
	/**
	 * 分类授权
	 */
	public function accessCategory(){
		if (IS_POST) $this->setAccessCategory();
		$auth_group     = M('AuthGroup')->where( array('status'=>array('egt','0'),'module'=>'admin','type'=>AuthGroupModel::TYPE_ADMIN) )->getfield('id,id,title,rules');
		$category_list  = D('Category')->getTree(0,'id,name,pid');

		$uid					= I('request.uid',0,'intval');
		$hashid					= I('request.hashid','');
		if ($uid > 0 && !empty($hashid)){
			if (!$this->checkId($uid,$hashid)) $this->error('非法参数！');
			$rules   		= M('member')->where(array('uid'=>$uid))->getField('catgory_rules');
			$rules			= empty($rules) ? '' : $rules;
		}else{
			$rules   		= AuthGroupModel::getCategoryOfGroup(I('get.id'));
			$rules			= empty($rules) ? '' : implode(',', $rules);
		}

		$this->assign('uid',     		$uid);
		$this->assign('hashid',     	$hashid);
		$this->assign('category_list',  $category_list);
		$this->assign('auth_group',     $auth_group);
		$this->assign('this_group', array('rules'=>$rules,'id'=>intval(I('get.id'))));
		$this->NavTitle 				= '分类授权';
		$this->display();
	}
	protected function setAccessVisit(){
		$rules						= I('post.rules');
		$id							= intval(I('post.id'));
		if ($id <= 0){
			$this->error('权限组不存在');
		}
		$updata['rules']			= '';
		if($rules){
			$updata['rules']		= trim($rules,',');
		}
		$updata['module'] 			= 'admin';
		$updata['type']   			= AuthGroupModel::TYPE_ADMIN;
		$updata['id'] 				= intval(I('post.id'));
		$AuthGroup       			= M('AuthGroup');
		$data 			 			= $AuthGroup->create($updata);
		if ( $data ) {
			if ( empty($data['id']) ) {
				$r = $AuthGroup->add();
			}else{
				$r = $AuthGroup->save();
			}
			if($r===false){
				$this->error('操作失败'.$AuthGroup->getError());
			} else{
				$this->success('操作成功!',U('index'));
			}
		}else{
			$this->error('操作失败'.$AuthGroup->getError());
		}
	}
	protected function setAccessMember(){
		$uid			= I('post.id',0,'intval');
		$hashid			= I('post.hashid','');
		if (!$this->checkId($uid,$hashid)) $this->error('非法参数！');
		$groupid		= I('post.groupid');
		//权限组设置，首先删除该用户的所有分组权限
		M('auth_group_access')->where(array('uid'=>$uid))->delete();
		if (!empty($groupid)){
			$groupid		= explode(',', $groupid);
			$groupList		= M('auth_group')->where(array('id'=>array('in',$groupid)))->field('id')->select();
			$access_group	= array();
			if (!empty($groupList)){
				foreach ($groupList as $k=>$v){
					$access_group[]	= array('uid'=>$uid,'group_id'=>$v['id']);
				}
			}
			if (!empty($access_group)){
				M('auth_group_access')->addAll($access_group);
			}
		}
		//特定权限设置
		$rules						= I('post.rules','');
		$updata['rules']			= !empty($rules) ? $rules : '';
		M('member')->where(array('uid'=>$uid))->setField($updata);
		$this->success('操作成功!',U('index'));
	}
	/**
	 * 分类授权
	 */
	public function setAccessCategory(){
		$cid						= I('post.rules');
		$id							= intval(I('post.id'));
		$uid						= I('request.uid',0,'intval');
		$hashid						= I('request.hashid','');
		if ($uid > 0 && !empty($hashid)){
			if (!$this->checkId($uid,$hashid)) $this->error('非法参数！');
			$updata['catgory_rules']			= '';
			if(!empty($cid)){
				$updata['catgory_rules']		= trim($cid,',');
			}
			M('member')->where(array('uid'=>$uid))->setField($updata);
			$this->success('操作成功!',Cookie('__forward__'));
		}else{
			if ($id <= 0) $this->error('权限组不存在');
			$cid						= empty($cid) ? '' : $cid;
			$AuthGroup 					= D('AuthGroup');
			if ( $AuthGroup->addToCategory($id,$cid) ){
				$this->success('操作成功',U('index'));
			}else{
				$this->error('操作失败');
			}
		}
	}
	/**
	 * 管理员用户组数据写入/更新
	 */
	protected function update(){
		$rules					= I('post.rules','');

		$updata['rules']		= empty($rules) ? '' : implode( ',' , array_unique(I('post.rules')));
		if (empty($rules)){ unset($updata['rules']);}
		$updata['id']			= I('post.id',0,'intval');
		$updata['module'] 		= 'admin';
		$updata['title']		= I('post.title','');
		$updata['description']	= I('post.description','');
		$updata['type']   		= AuthGroupModel::TYPE_ADMIN;
		$AuthGroup       		= D('AuthGroup');
		$res 					= $AuthGroup->update($updata);
		if (!empty($res)){
			$ac				= array('更新','新增');
			$this->success($ac[$res['ac']].'成功!',Cookie('__forward__'));
		}else{
			$this->error('操作失败:'.$AuthGroup->getError());
		}
	}

	/**
	 * 状态修改
	 */
	public function changeStatus($method=null){
		if ( empty($_REQUEST['id']) ) {
			$this->error('请选择要操作的数据!');
		}
		switch ( strtolower($method) ){
			case 'forbidgroup':
				$this->forbid('AuthGroup',array(),array('url'=>Cookie('__forward__')));
				break;
			case 'resumegroup':
				$this->resume('AuthGroup',array(),array('url'=>Cookie('__forward__')));
				break;
			case 'deletegroup':
				$this->delete('AuthGroup',array(),array('url'=>Cookie('__forward__')));
				break;
			default:
				$this->error($method.'参数非法');
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
		array('fieldName'=>'用户组名称','fieldValue'=>'title','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入用户组名称"'),
		array('fieldName'=>'用户组描述','fieldValue'=>'description','fieldType'=>'textarea','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入用户组描述"'),
		array('fieldName'=>'隐藏域','fieldValue'=>array('id'),'fieldType'=>'hidden','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder=""'),
		);

		$map			= array('status'=>array('egt','0'),'module'=>'admin','type'=>AuthGroupModel::TYPE_ADMIN);
		$auth_group 	= M('AuthGroup')->where( $map )->getfield('id,id,title');
		$authData		= array();
		if (!empty($auth_group)){
			foreach ($auth_group as $k=>$v){
				$authData[$k] = $v['title'];
			}
		}

		$FormData[1] = array(
		array('fieldName'=>'分组变更','fieldValue'=>'groupid','fieldType'=>'radio','isMust'=>0,'fieldData'=>$authData,'attrExtend'=>'placeholder="请输入用户组名称"'),
		array('fieldName'=>'隐藏域','fieldValue'=>array('id','hashid'),'fieldType'=>'hidden','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder=""'),
		);
		return $FormData[$index];
	}
	/**
	 * 后台节点配置的url作为规则存入auth_rule
	 * 执行新节点的插入,已有节点的更新,无效规则的删除三项任务
	 */
	protected function updateRules(){
		//需要新增的节点必然位于$nodes
		$nodes    = $this->returnNodes(false);

		$AuthRule = M('AuthRule');
		$map      = array('module'=>'admin','type'=>array('in','1,2'));//status全部取出,以进行更新
		//需要更新和删除的节点必然位于$rules
		$rules    = $AuthRule->where($map)->order('name')->select();

		//构建insert数据
		$data     = array();//保存需要插入和更新的新节点
		foreach ($nodes as $value){
			$temp['name']   = $value['url'];
			$temp['title']  = $value['title'];
			$temp['module'] = 'admin';
			$temp['menuid'] = $value['id'];
			if($value['pid'] >0){
				$temp['type'] = AuthRuleModel::RULE_URL;
			}else{
				$temp['type'] = AuthRuleModel::RULE_MAIN;
			}
			$temp['status']   = 1;
			$data[strtolower($temp['name'].$temp['module'].$temp['type'])] = $temp;//去除重复项
		}

		$update = array();//保存需要更新的节点
		$ids    = array();//保存需要删除的节点的id
		foreach ($rules as $index=>$rule){
			$key = strtolower($rule['name'].$rule['module'].$rule['type']);
			if ( isset($data[$key]) ) {//如果数据库中的规则与配置的节点匹配,说明是需要更新的节点
				$data[$key]['id'] = $rule['id'];//为需要更新的节点补充id值
				$update[] = $data[$key];
				unset($data[$key]);
				unset($rules[$index]);
				unset($rule['condition']);
				$diff[$rule['id']]=$rule;
			}elseif($rule['status']==1){
				$ids[] = $rule['id'];
			}
		}
		if ( count($update) ) {
			foreach ($update as $k=>$row){
				if ( $row!=$diff[$row['id']] ) {
					$AuthRule->where(array('id'=>$row['id']))->save($row);
				}
			}
		}
		if ( count($ids) ) {
			$AuthRule->where( array( 'id'=>array('IN',implode(',',$ids)) ) )->save(array('status'=>-1));
			//删除规则是否需要从每个用户组的访问授权表中移除该规则?
		}
		if( count($data) ){
			$AuthRule->addAll(array_values($data));
		}
		if ( $AuthRule->getDbError() ) {
			trace('['.__METHOD__.']:'.$AuthRule->getDbError());
			return false;
		}else{
			return true;
		}
	}
}
?>