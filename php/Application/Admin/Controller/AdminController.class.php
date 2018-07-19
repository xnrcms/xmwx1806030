<?php
namespace Admin\Controller;
use Think\Controller;
use Admin\Model\AuthRuleModel;
use Admin\Model\AuthGroupModel;
use User\Api\UserApi;
/**
 * 后台首页控制器
 */
class AdminController extends Controller {
	/**
	 * 后台控制器初始化
	 */
	protected function _initialize(){
		// 获取当前用户ID
		$this->AuthLogin();
		define('UID',is_login());
		if( !UID ) $this->GoLogin();// 还没登录 跳转到登录页面
		/* 读取数据库中的配置 */
		$config 	= S('DB_CONFIG_DATA');
		if(!$config){
			$config = api('Config/lists');
			S('DB_CONFIG_DATA',$config);
		}
		//添加配置
		C($config);
		// 是否是超级管理员
		define('IS_ROOT',   is_administrator());
		if(!IS_ROOT && C('ADMIN_ALLOW_IP')){
			// 检查IP地址访问
			if(!in_array(get_client_ip(),explode(',',C('ADMIN_ALLOW_IP')))) $this->error('403:禁止访问');
		}
		// 检测访问权限
		$access 			= $this->accessControl();
		if ( $access === false ) {
			$this->error('403:禁止访问');
		}elseif( $access === null ){
			$dynamic        = $this->checkDynamic();//检测分类栏目有关的各项动态权限
			if( $dynamic === null ){
				//检测非动态权限
				$rule  = strtolower(MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME);
				if ( !$this->checkRule($rule,array('in','1,2')) ){
					$this->error('未授权访问!');
				}
			}elseif( $dynamic === false ){
				$this->error('未授权访问!');
			}
		}
		$this->menuid			= I('get.menuid',0);//菜单ID
		$this->usergroup		= get_group_list(UID);//获取用户所在组
		$this->extends_param	= '&menuid='.$this->menuid;
		$this->userInfo			= $this->userInfo();
		$business_uid = M('user')->where(array('phone'=>$this->userInfo['username']))->getField('id');
		define('BUID',$business_uid);
	}
	/**
	 * 获取用户信息
	 */
	final protected function userInfo(){
		$userInfo				= array();
		if (UID){
			//获取数据
			$MainTab			= 'ucenter_member';
			$MainAlias			= 'main';
			$MainField			= array('id','username','mobile','email');
			//主表模型
			$MainModel 			= M($MainTab)->alias($MainAlias);
			$RelationTab		= array(
				'member'=>array('Ralias'=>'me','Ron'=>'me ON me.uid=main.id','Rfield'=>array()),
			);
			$RelationTab		= $this->getRelationTab($RelationTab);
			$tables	  			= $RelationTab['tables'];
			$RelationFields		= $RelationTab['fields'];
			$model				= !empty($tables) ? $MainModel->join ( $tables ,'LEFT') : $MainModel;
			//检索条件
			$map[$MainAlias.'.id']  	= UID;
			//检索字段
			$fields						= (empty($MainField) ? $this->get_fields_string($MainModel->getDbFields(),$MainAlias).',' : $this->get_fields_string($MainField,$MainAlias).',') . $RelationFields;
			$fields						= trim($fields,',');
			//用户数据
			$userInfo 					= $model->where($map)->field($fields)->find();
			$userInfo['nickname']		= empty($userInfo['nickname']) ? $userInfo['username'] : $userInfo['nickname'];
			if (IS_ROOT){
				$groupname				= '超级管理员';
			}else{
				$groupid				= M('auth_group_access')->where(array('uid'=>UID))->getField('group_id');
				$groupname				= M('auth_group')->where(array('id'=>$groupid))->getField('title');
			}
			$userInfo['groupname']		= empty($groupname) ? '自由组' : $groupname;
		}
		return $userInfo;
	}
	/**
	 * 权限检测
	 * @param string  $rule    检测的规则
	 * @param string  $mode    check模式
	 * @return boolean
	 */
	final protected function checkRule($rule, $type=AuthRuleModel::RULE_URL, $mode='url'){
		if(IS_ROOT){
			return true;//管理员允许访问任何页面
		}
		static $Auth    =   null;
		if (!$Auth) {
			$Auth       =   new \Think\Auth();
		}
		if(!$Auth->check($rule,UID,$type,$mode)){
			return false;
		}
		return true;
	}

	/**
	 * 检测是否是需要动态判断的权限
	 * @return boolean|null
	 *      返回true则表示当前访问有权限
	 *      返回false则表示当前访问无权限
	 *      返回null，则会进入checkRule根据节点授权判断权限
	 */
	protected function checkDynamic(){
		if(IS_ROOT){
			return true;//管理员允许访问任何页面
		}
		return null;//不明,需checkRule
	}


	/**
	 * action访问控制,在 **登陆成功** 后执行的第一项权限检测任务
	 *
	 * @return boolean|null  返回值必须使用 `===` 进行判断
	 *
	 *   返回 **false**, 不允许任何人访问(超管除外)
	 *   返回 **true**, 允许任何管理员访问,无需执行节点权限检测
	 *   返回 **null**, 需要继续执行节点权限检测决定是否允许访问
	 */
	final protected function accessControl(){
		if(IS_ROOT){
			return true;//管理员允许访问任何页面
		}
		$allow = C('ALLOW_VISIT');
		$deny  = C('DENY_VISIT');
		$check = strtolower(CONTROLLER_NAME.'/'.ACTION_NAME);
		if ( !empty($deny)  && in_array_case($check,$deny) ) {
			return false;//非超管禁止访问deny中的方法
		}
		if ( !empty($allow) && in_array_case($check,$allow) ) {
			return true;
		}
		return null;//需要检测节点权限
	}

	/**
	 * 对数据表中的单行或多行记录执行修改 GET参数id为数字或逗号分隔的数字
	 *
	 * @param string $model 模型名称,供M函数使用的参数
	 * @param array  $data  修改的数据
	 * @param array  $where 查询时的where()方法的参数
	 * @param array  $msg   执行正确和错误的消息 array('success'=>'','error'=>'', 'url'=>'','ajax'=>false)
	 *                     	url为跳转页面,ajax是否ajax方式(数字则为倒数计时秒数)
	 */
	final protected function editRow ( $model ,$data, $where , $msg ){
		$id    	= array_unique((array)I('id',0));
		$id    	= is_array($id) ? implode(',',$id) : $id;
		$where1	= !$id ? array() : array('id' => array('in', $id ));
		$where2	= (array)$where;
		$where 	= array_merge( $where1 ,$where2 );
		$msg   	= array_merge( array( 'success'=>'操作成功！', 'error'=>'操作失败！', 'url'=>'' ,'ajax'=>IS_AJAX) , (array)$msg );
		if( M($model)->where($where)->save($data)!==false ) {
			$this->success($msg['success'],$msg['url'],$msg['ajax']);
		}else{
			$this->error($msg['error'],$msg['url'],$msg['ajax']);
		}
	}

	/**
	 * 禁用条目
	 * @param string $model 模型名称,供D函数使用的参数
	 * @param array  $where 查询时的 where()方法的参数
	 * @param array  $msg   执行正确和错误的消息,可以设置四个元素 array('success'=>'','error'=>'', 'url'=>'','ajax'=>false)
	 *                     	url为跳转页面,ajax是否ajax方式(数字则为倒数计时秒数)
	 */
	protected function forbid ( $model , $where = array() , $msg = array( 'success'=>'状态禁用成功！', 'error'=>'状态禁用失败！')){
		$data    =  array('status' => 0);
		$this->editRow( $model , $data, $where, $msg);
	}
	protected function forbidForGoods ( $model , $where = array() , $msg = array( 'success'=>'状态禁用成功！', 'error'=>'状态禁用失败！')){
		$data    =  array('status' => 2);
		$this->editRow( $model , $data, $where, $msg);
	}

	/**
	 * 恢复条目
	 * @param string $model 模型名称,供D函数使用的参数
	 * @param array  $where 查询时的where()方法的参数
	 * @param array  $msg   执行正确和错误的消息 array('success'=>'','error'=>'', 'url'=>'','ajax'=>false)
	 *                     	url为跳转页面,ajax是否ajax方式(数字则为倒数计时秒数)
	 */
	protected function resume (  $model , $where = array() , $msg = array( 'success'=>'状态恢复成功！', 'error'=>'状态恢复失败！')){
		$data    =  array('status' => 1);
		$this->editRow(   $model , $data, $where, $msg);
	}

	/**
	 * 还原条目
	 * @param string $model 模型名称,供D函数使用的参数
	 * @param array  $where 查询时的where()方法的参数
	 * @param array  $msg   执行正确和错误的消息 array('success'=>'','error'=>'', 'url'=>'','ajax'=>false)
	 *                     	url为跳转页面,ajax是否ajax方式(数字则为倒数计时秒数)
	 */
	protected function restore (  $model , $where = array() , $msg = array( 'success'=>'状态还原成功！', 'error'=>'状态还原失败！')){
		$data    = array('status' => 1);
		$where   = array_merge(array('status' => -1),$where);
		$this->editRow(   $model , $data, $where, $msg);
	}

	/**
	 * 条目假删除
	 * @param string $model 模型名称,供D函数使用的参数
	 * @param array  $where 查询时的where()方法的参数
	 * @param array  $msg   执行正确和错误的消息 array('success'=>'','error'=>'', 'url'=>'','ajax'=>false)
	 *                    	url为跳转页面,ajax是否ajax方式(数字则为倒数计时秒数)
	 */
	protected function delete ( $model , $where = array() , $msg = array( 'success'=>'删除成功！', 'error'=>'删除失败！')) {
		$data['status']         =   -1;
		$data['update_time']    =   NOW_TIME;
		$this->editRow(   $model , $data, $where, $msg);
	}

	/**
	 * 设置一条或者多条数据的状态
	 */
	public function setStatus($Model=CONTROLLER_NAME){

		$ids    =   I('request.ids');
		$status =   I('request.status');
		if(empty($ids)){
			$this->error('请选择要操作的数据');
		}

		$map['id'] = array('in',$ids);
		switch ($status){
			case -1 :
				$this->delete($Model, $map, array('success'=>'删除成功','error'=>'删除失败','url'=>Cookie('__forward__')));
				break;
			case 0  :
				$this->forbid($Model, $map, array('success'=>'禁用成功','error'=>'禁用失败','url'=>Cookie('__forward__')));
				break;
			case 1  :
				$this->resume($Model, $map, array('success'=>'启用成功','error'=>'启用失败','url'=>Cookie('__forward__')));
				break;
			default :
				$this->error('参数错误');
				break;
		}
	}
	public function setGoodsStatus($Model=CONTROLLER_NAME){

		$ids    =   I('request.ids');
		$status =   I('request.status');
		if(empty($ids)){
			$this->error('请选择要操作的数据');
		}

		$map['id'] = array('in',$ids);
		switch ($status){
			case -1 :
				$this->delete($Model, $map, array('success'=>'删除成功','error'=>'删除失败','url'=>Cookie('__forward__')));
				break;
			case 2  :
				$this->forbidForGoods($Model, $map, array('success'=>'下架成功','error'=>'下架失败','url'=>Cookie('__forward__')));
				break;
			case 1  :
				$this->resume($Model, $map, array('success'=>'上架成功','error'=>'上架失败','url'=>Cookie('__forward__')));
				break;
			default :
				$this->error('参数错误');
				break;
		}
	}
	/**
	 * 获取控制器菜单数组,二级菜单元素位于一级菜单的'_child'元素中
	 */
	final public function getMenus($controller=CONTROLLER_NAME,$isleft=false){
		if(empty($menus)){
			// 获取主菜单
			$where          	= array();
			$where['pid']   	= 0;
			$where['hide']  	= 0;
			$where['status']  	= 1;
			$where['group']  	= array('NOT IN','TOPMENU,RIGHTMENU');
			$isDev				= C('DEVELOP_MODE');
			if(!$isDev){
				$where['is_dev']	= 0;
			}
			$menus['main']  	= M('Menu')->where($where)->order('sort asc')->select();
			$menus['child'] 	= array(); //设置子节点
			//高亮主菜单
			$controller			= empty($controller) ? $menus['main'][0]['url'] : $controller;
			$paths				= $isleft == true ? $controller : $controller.'/'. ACTION_NAME;
			$current 			= M('Menu')->where(array('url'=>array('like','%'.$paths.'%'),'status'=>1))->field('id')->find();
			if($current){
				$nav = D('Menu')->getPath($current['id']);
				$nav_first_title = $nav[0]['title'];

				foreach ($menus['main'] as $key => $item) {
					if (!is_array($item) || empty($item['title']) || empty($item['url']) ) {
						$this->error('控制器基类$menus属性元素配置有误');
					}
					if( stripos($item['url'],MODULE_NAME)!==0 ){
						$item['url'] = MODULE_NAME.'/'.$item['url'];
					}
					// 判断主菜单权限
					if ( !IS_ROOT && !$this->checkRule($item['url'],AuthRuleModel::RULE_MAIN,null) ) {
						unset($menus['main'][$key]);
						continue;//继续循环
					}

					// 获取当前主菜单的子菜单项
					if($item['title'] == $nav_first_title){
						$menus['main'][$key]['class']='current';
						//生成child树
						$where          	= array();
						$where['pid']   	= $item['id'];
						$where['hide']  	= 0;
						$where['status']  	= 1;
						$where['group']  	= array('NOT IN','TOPMENU,RIGHTMENU');
						if(!$isDev){ // 是否开发者模式
							$where['is_dev']    =   0;
						}
						$groups = M('Menu')->where($where)->distinct(true)->field("`group`")->select();
						if($groups){
							$groups = array_column($groups, 'group');
						}else{
							$groups =   array();
						}

						//获取二级分类的合法url
						$where          	= array();
						$where['pid']   	= $item['id'];
						$where['hide']  	= 0;
						$where['status']  	= 1;
						if(!$isDev){ // 是否开发者模式
							$where['is_dev']    =   0;
						}
						$second_urls = M('Menu')->where($where)->getField('id,url');

						if(!IS_ROOT){
							// 检测菜单权限
							$to_check_urls = array();
							foreach ($second_urls as $key=>$to_check_url) {
								if( stripos($to_check_url,MODULE_NAME)!==0 ){
									$rule = MODULE_NAME.'/'.$to_check_url;
								}else{
									$rule = $to_check_url;
								}
								if($this->checkRule($rule, AuthRuleModel::RULE_URL,null))
								$to_check_urls[] = $to_check_url;
							}
						}
						// 按照分组生成子菜单树
						foreach ($groups as $g) {
							$map = array('group'=>$g);
							if(isset($to_check_urls)){
								if(empty($to_check_urls)){
									// 没有任何权限
									continue;
								}else{
									$map['url'] = array('in', $to_check_urls);
								}
							}
							$map['pid'] 	= $item['id'];
							$map['hide']    = 0;
							$map['status'] 	= 1;
							if(!$isDev){ // 是否开发者模式
								$map['is_dev']  =   0;
							}
							$menuList = M('Menu')->where($map)->field('id,pid,title,url,tip')->order('sort asc')->select();
							$menus['child'][$g] = list_to_tree($menuList, 'id', 'pid', 'operater', $item['id']);
							if(empty($menus['child'][$g])){
								unset($menus['child'][$g]);
							}
						}
						if($menus['child'] === array()){
							//$this->error('主菜单下缺少子菜单，请去系统=》后台菜单管理里添加');
						}
					}
				}
			}
			// session('ADMIN_MENU_LIST'.$controller,$menus);
		}
		return $menus;
	}
	//子菜单权限
	final public function getSonMenu($menuid){
		//操作菜单
		$SonMenu		= D('Menu')->getSonMenu($menuid,array('status'=>1,'group'=>array('IN',array('TOPMENU','RIGHTMENU'))));
		if (!empty($SonMenu)){
			foreach ($SonMenu as $k=>$v){
				foreach ($v as $kk=>$vv){
					if( stripos($vv['url'],MODULE_NAME)!==0 ){
						$vv['url'] = MODULE_NAME.'/'.$vv['url'];
					}
					if ( !IS_ROOT && !$this->checkRule($vv['url'],AuthRuleModel::RULE_URL,null) ) {
						unset($SonMenu[$k][$kk]);
						continue;//继续循环
					}
				}
			}
		}else{
			return array('TOPMENU'=>array(),'RIGHTMENU'=>array());
		}
		return $SonMenu;
	}
	/**
	 * 返回后台节点数据
	 * @param boolean $tree    是否返回多维数组结构(生成菜单时用到),为false返回一维数组(生成权限节点时用到)
	 * @retrun array
	 *
	 * 注意,返回的主菜单节点数组中有'controller'元素,以供区分子节点和主节点
	 */
	final protected function returnNodes($tree = true){
		static $tree_nodes = array();
		if ( $tree && !empty($tree_nodes[(int)$tree]) ) {
			return $tree_nodes[$tree];
		}
		if((int)$tree){
			$list = M('Menu')->field('id,pid,title,url,tip,hide')->where(array('status'=>1))->order('sort asc')->select();
			foreach ($list as $key => $value) {
				if( stripos($value['url'],MODULE_NAME)!==0 ){
					$list[$key]['url'] = MODULE_NAME.'/'.$value['url'];
				}
			}
			$nodes = list_to_tree($list,$pk='id',$pid='pid',$child='operator',$root=0);
			foreach ($nodes as $key => $value) {
				if(!empty($value['operator'])){
					$nodes[$key]['child'] = $value['operator'];
					unset($nodes[$key]['operator']);
				}
			}
		}else{
			$nodes = M('Menu')->field('id,title,url,tip,pid')->where(array('status'=>1))->order('sort asc')->select();
			foreach ($nodes as $key => $value) {
				if( stripos($value['url'],MODULE_NAME)!==0 ){
					$nodes[$key]['url'] = MODULE_NAME.'/'.$value['url'];
				}
			}
		}
		$tree_nodes[(int)$tree]   = $nodes;
		return $nodes;
	}

	//通用分页列表数据集获取方法
	protected function getLists ($model,$where=array(),$order='',$field=true,$page=1,$limit=0,$ispage=false,$group=''){
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
		if($group!==''){
			$options['group'] = $group;
		}
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
		$this->_remainder	= $remainder >= 0 ? $remainder : 0;
		$this->_total		= $total ? $total : 0;
		$this->_page		= !empty($p)? $p : '';
		if ($ispage == true){
			return $model->field($field)->select();
		}
		return $model->field($field)->limit($listLimit)->page($page)->select();
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
						$fields				.= $this->get_fields_string(M($Rtables)->getDbFields(),$Ralias).',';
					}elseif (is_string($Rfield)){
						$fields				.= $this->get_fields_string(implode(',', $Rfield),$Ralias).',';
					}elseif (is_array($Rfield)){
						$fields				.= $this->get_fields_string($Rfield,$Ralias).',';
					}
				}
			}
		}
		return array('tables'=>$tables,'fields'=>$fields);
	}
	/**
	 * 通用分页列表数据集获取方法
	 *
	 *  可以通过url参数传递where条件,例如:  index.html?name=asdfasdfasdfddds
	 *  可以通过url空值排序字段和方式,例如: index.html?_field=id&_order=asc
	 *  可以通过url参数r指定每页数据条数,例如: index.html?r=5
	 *
	 * @param sting|Model  $model   模型名或模型实例
	 * @param array        $where   where查询条件(优先级: $where>$_REQUEST>模型设定)
	 * @param array|string $order   排序条件,传入null时使用sql默认排序或模型属性(优先级最高);
	 *                              请求参数中如果指定了_order和_field则据此排序(优先级第二);
	 *                              否则使用$order参数(如果$order参数,且模型也没有设定过order,则取主键降序);
	 *
	 * @param array        $base    基本的查询条件
	 * @param boolean      $field   单表模型用不到该参数,要用在多表join时为field()方法指定参数
	 * @return array|false
	 * 返回数据集
	 */
	protected function lists ($model,$where=array(),$order='',$base = array('status'=>array('egt',0)),$field=true){
		$options    =   array();
		$REQUEST    =   (array)I('request.');
		if(is_string($model))
		{
			$model  =   M($model);
		}

		$OPT        =   new \ReflectionProperty($model,'options');
		$OPT->setAccessible(true);

		$pk         =   $model->getPk();
		if($order===null){
			//order置空
		}else if ( isset($REQUEST['_order']) && isset($REQUEST['_field']) && in_array(strtolower($REQUEST['_order']),array('desc','asc')) ) {
			$options['order'] = '`'.$REQUEST['_field'].'` '.$REQUEST['_order'];
		}elseif( $order==='' && empty($options['order']) && !empty($pk) ){
			$options['order'] = $pk.' desc';
		}elseif($order){
			$options['order'] = $order;
		}
		unset($REQUEST['_order'],$REQUEST['_field']);

		$options['where'] = array_filter(array_merge( (array)$base,(array)$where ),'array_filter_backcall');
		if( empty($options['where'])){
			unset($options['where']);
		}
		$options      =   array_merge( (array)$OPT->getValue($model), $options );
		$total        =   $model->where($options['where'])->count();

		if( isset($REQUEST['r']) ){
			$listRows = (int)$REQUEST['r'];
		}else{
			$listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
		}
		$page = new \Think\Page($total, $listRows, $REQUEST);
		if($total>$listRows){
			$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
		}
		$p = trim($page->show());
		$this->assign('_page', !empty($p)? $p : '');
		$this->assign('_total',$total);
		$options['limit'] = $page->firstRow.','.$page->listRows;
		$model->setProperty('options',$options);
		return $model->field($field)->select();
	}
	protected function lists2 ($model,$where=array(),$order='',$field=true){
		$options    =   array();
		$REQUEST    =   (array)I('request.');
		if(is_string($model))
		{
			$model  =   M($model);
		}

		$OPT        =   new \ReflectionProperty($model,'options');
		$OPT->setAccessible(true);

		$pk         =   $model->getPk();
		if($order===null){
			//order置空
		}else if ( isset($REQUEST['_order']) && isset($REQUEST['_field']) && in_array(strtolower($REQUEST['_order']),array('desc','asc')) ) {
			$options['order'] = '`'.$REQUEST['_field'].'` '.$REQUEST['_order'];
		}elseif( $order==='' && empty($options['order']) && !empty($pk) ){
			$options['order'] = $pk.' desc';
		}elseif($order){
			$options['order'] = $order;
		}
		unset($REQUEST['_order'],$REQUEST['_field']);

		if(empty($where)){
			$where  =   array('status'=>array('egt',0));
		}
		if( !empty($where)){
			$options['where']   =   $where;
		}
		$options      =   array_merge( (array)$OPT->getValue($model), $options );
		$total        =   $model->where($options['where'])->count();

		if( isset($REQUEST['r']) ){
			$listRows = (int)$REQUEST['r'];
		}else{
			$listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
		}
		$page = new \Think\Page($total, $listRows, $REQUEST);
		if($total>$listRows){
			$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
		}
		$p =$page->show();
		$this->assign('_page', $p? $p: '');
		$this->assign('_total',$total);
		$options['limit'] = $page->firstRow.','.$page->listRows;

		$model->setProperty('options',$options);

		return $model->field($field)->select();
	}

	function getInfo($model,$where=array(),$base = array('status'=>array('egt',0)),$field=true)
	{
		$options    =   array();
		$model  	=   $this->modelObj($model);
		$OPT        =   new \ReflectionProperty($model,'options');
		$OPT->setAccessible(true);

		$options['where'] = array_filter(array_merge( (array)$base,(array)$where ),'array_filter_backcall');
		if( empty($options['where']))
		{
			unset($options['where']);
		}
		$options      =   array_merge( (array)$OPT->getValue($model), $options );
		$model->setProperty('options',$options);
		return $model->field($field)->find();
	}
	protected function modelObj($model)
	{
		$modelObj = null;
		if(is_array($model))
		{
			$prefix	= C('DB_PREFIX');
			$m_tab	= $model['mtab'];
			$s_tabs	= $model['stab'];
			if (!empty($m_tab))
			{
				$smodel = array();
				if (!empty($s_tabs))
				{
					foreach ($s_tabs as $key=>$val)
					{
						//$val格式必须是  表1-表2,别名1-别名2-ID1-ID2
						if (!empty($val))
						{
							$stab = explode('-', $val);
							if (!empty($stab[0]) && !empty($stab[1]) && !empty($stab[2]) && !empty($stab[3]) && !empty($stab[4]))
							{
								$need 		= array('tabName1','aliasNmae1','aliasNmae2','ID1','ID2');
								$by			= array(strtolower($stab[0]),$stab[1],$stab[2],$stab[3],$stab[4]);
								$str		= $prefix.'tabName1 AS aliasNmae1 ON aliasNmae1.ID1=aliasNmae2.ID2';
								$smodel[] 	= str_replace($need, $by, $str);;
							}
						}
					}
					if ($smodel)
					{
						$m_tab 		= strtolower($m_tab);
						$modelObj   = M()->table( $prefix.$m_tab.' AS m' )->join ( $smodel ,'LEFT');
					}
				}
				else
				{
					$modelObj  =   M($m_tab);
				}
			}
		}
		else
		{
			$modelObj  =   M($model);
		}
		return $modelObj;
	}
	/**
	 * 处理文档列表显示
	 * @param array $list 列表数据
	 * @param integer $model_id 模型id
	 */
	protected function parseDocumentList($list,$model_id=null){
		$attrList = get_model_attribute($model_id ? $model_id : 1,false,'id,name,type,extra');
		// 对列表数据进行显示处理
		if(is_array($list)){
			foreach ($list as $k=>$data){
				foreach($data as $key=>$val){
					if(isset($attrList[$key])){
						$extra      =   $attrList[$key]['extra'];
						$type       =   $attrList[$key]['type'];
						if('select'== $type || 'checkbox' == $type || 'radio' == $type || 'bool' == $type) {
							// 枚举/多选/单选/布尔型
							$options    =   parse_field_attr($extra);
							if($options && array_key_exists($val,$options)) {
								$data[$key]    =   $options[$val];
							}
						}elseif('date'==$type){ // 日期型
							$data[$key]    =   date('Y-m-d',$val);
						}elseif('datetime' == $type){ // 时间型
							$data[$key]    =   date('Y-m-d H:i',$val);
						}
					}
				}
				$list[$k]   =   $data;
			}
		}
		return $list;
	}
	protected function get_fields_string($fields,$prefix=''){
		if ($prefix != ''){
			foreach ($fields as $key=>$val){
				//$fields[$key] = $prefix.'.'.$val;
				if(preg_match('/\([^\)].*?\)/', $val) === 1){
					$fields[$key] = $val;
				}else{
					$fields[$key] = $prefix.'.'.$val;
				}
			}
		}
		return implode(',', $fields);
	}
	/**
	 * 执行登录跳转
	 */
	protected function GoLogin()
	{
		if (IS_AJAX)
		{
			$this->error('您还没有登录!',U('Public/login'));
		}
		else
		{
			if (isset($_SERVER['HTTP_REFERER']))
			{
				exit('<script>top.location.href="'.U('Public/login').'"</script>');
			}
			else
			{
				header("Location:".U('Public/login'));exit;
			}
		}
	}
	//执行自动登录
	protected function AuthLogin(){
		$cookie_username	= cookie(md5('admin_username'.C('DATA_AUTH_KEY')));
		$cookie_password	= cookie(md5('admin_password'.C('DATA_AUTH_KEY')));
		if(!is_login() && $cookie_username && $cookie_password){
			$username	= FauthCode($cookie_username,'DECODE');
			$password	= FauthCode($cookie_password,'DECODE');
			$username 	= safe_replace($username);//过滤
			/* 调用UC登录接口登录 */
			$User 		= new UserApi();
			$uid 		= $User->login($username, $password);
			if(0 < $uid){ //UC登录成功
				/* 登录用户 */
				$Member = D('Member');
				if($Member->login($uid)){ //登录用户
					if ($remember == 1){
						//保存session信息
						cookie(md5('admin_username'.C('DATA_AUTH_KEY')),FauthCode($mobile,'ENCODE'),2592000); // 指定cookie保存30天时间
						cookie(md5('admin_password'.C('DATA_AUTH_KEY')),FauthCode($pwd,'ENCODE'),2592000); // 指定cookie保存30天时间
					}
				} else {
					session('[destroy]');
					cookie(null);
				}

			} else { //登录失败
				session('[destroy]');
				cookie(null);
			}
		}
	}
	//模型校验
	protected function checkModel($model_id)
	{
		$modelInfo 			= M('Model')->getById($model_id);//获取模型信息
		$names				= explode('system', $modelInfo['name']);
		if (!empty($names[0])){
			$modelInfo['name']	= $names[0];
		}
		if (empty($modelInfo)) {
			$this->error('数据模型错误,操作失败');
		}
		return $modelInfo;
	}
	protected function checkSearch($modelInfo)
	{
		//基础
		$back_data['search_key']['search_key_map']		= array();
		$back_data['search_key']['search_key_text'] 	= array();
		$search_key			= $modelInfo['search_key'];
		$search_key			= explode(',', $search_key);
		$maps['name']		= array('in',$search_key);
		$maps['model_id']	= $modelInfo['id'];
		if ($modelInfo['extend'] == 1) $maps['model_id']	= array('in',array(1,$modelInfo['id']));
		$search_fields		= M('attribute')->field('title,type,name')->where($maps)->select();
		$search_key_text	= '';
		$keyword			= I('request.find_keyword','');
		if (!empty($search_fields))
		{
			$search_key_text	= '请输入';
			foreach ($search_fields as $val)
			{
				$search_key_text .= $val['title'].'/';
				$search_key_map[$val['name']]		= array('like','%'.$keyword.'%');
			}
			$search_key_text			= trim($search_key_text,'/').'进行搜索';
			$search_key_map['_logic'] 	= 'or';
		}
		$back_data['search_key']['search_key_text']		= $search_key_text;
		$back_data['search_key']['search_key_map']		= empty($keyword) ? array() : $search_key_map;
		//高级
		$back_data['search_list']['search_list_map']	= array();
		$back_data['search_list']['search_list_text'] 	= array();
		$search_list		= $modelInfo['search_list'];
		$search_list		= explode(',', $search_list);
		$maps['name']		= array('in',$search_list);
		$search_fields		= M('attribute')->field('title,type,name')->where($maps)->select();
		if (!empty($search_fields))
		{
			foreach ($search_fields as &$val)
			{

				if ($val['type'] == 'datetime')
				{
					$start_time		= I('request.'.$val['name'].'_start');
					$end_time		= I('request.'.$val['name'].'_end');
					$val['value']	= $start_time;
					$val['value2']	= $end_time;
					if (!empty($start_time))
					{
						$back_data['search_list']['search_list_map'][$val['name']]	= array('egt',strtotime($start_time));
					}
					if (!empty($end_time))
					{
						$back_data['search_list']['search_list_map'][$val['name']]	= array('elt',strtotime($end_time));
					}
					if (!empty($start_time) && !empty($end_time))
					{
						$back_data['search_list']['search_list_map'][$val['name']]	= array(array('egt',strtotime($start_time)),array('elt',strtotime($end_time)));
					}
				}
				else
				{
					$search_value													= I('request.'.$val['name']);
					if (!empty($search_value))
					{
						$back_data['search_list']['search_list_map'][$val['name']]	= $search_value;
					}
					$val['value']	= $search_value;
					$val['value2']	= '';
				}
				$back_data['search_list']['search_list_text'][]						= $val;
			}
		}
		return $back_data;
	}
	//检验ID
	protected function checkId($id,$hashid)
	{
		return (safely_id($id) === $hashid && $id >0 && !empty($hashid)) ? true : false;
	}
}