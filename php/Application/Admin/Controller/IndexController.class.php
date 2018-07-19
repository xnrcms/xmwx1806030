<?php
namespace Admin\Controller;
use Admin\Model\AuthGroupModel;
use User\Api\UserApi as UserApi;
/**
 * 后台首页控制器
 */
class IndexController extends AdminController {
	/**
	 * 后台首页
	 */
	public function index(){
		if(UID){
			$this->NavTitle = '管理首页';
			$code 			= trim(I('get.code'));
			$code 			= str_replace('-', '/', $code);
				
			$where          	= array();
			$where['pid']   	= 0;
			$where['hide']  	= 0;
			$where['status']  	= 1;
			$where['group']  	= array('NOT IN','TOPMENU,RIGHTMENU');
			$isDev				= C('DEVELOP_MODE');
			if(!$isDev){
				$where['is_dev']	= 0;
			}
				
			$defcode		= M('Menu')->where($where)->order('sort ASC')->getField('url');
			$code			= !empty($code) ? $code : $defcode;
			$catmenu		= C('CAT_BIND_MENU');
			if (!empty($catmenu)){
				foreach ($catmenu as $k=>$v){
					$paths		= explode('-', $v);
					if ($code == $paths[0]){
						$this->menuid		= $k;
						$this->getCatMenu($code,$paths[1]);
					}
				}
			}
			$Menu 			= $this->getMenus($code,true);
			$this->assign('TopMenu',!empty($Menu['main']) ? $Menu['main'] : array());
			$this->assign('LeftMenu',!empty($Menu['child']) ? $Menu['child'] : array());
			$this->display();
		} else {
			$this->redirect('Public/login');
		}
	}
	public function center()
	{
		$minfo							= M('member')->where(array('uid'=>UID))->field(array('last_login_ip','last_login_time','login'))->find();
		if (!empty($minfo)){
			$minfo['last_login_time']	= date('Y-m-d H:i:s',$minfo['last_login_time']);
			$minfo['last_login_ip']		= long2ip($minfo['last_login_ip']);
		}else{
			$minfo['last_login_ip']		= '';
			$minfo['last_login_time']	= '';
			$minfo['login']				= 0;
		}
		$arRuntime 			= explode(",", exec('uptime'));print_r(exec('uptime'));
		$info = array(
            'hostname' 				=> gethostbyaddr($_SERVER['REMOTE_ADDR']),
            'hostip' 				=> get_client_ip(0),
            'hostdomain' 			=> $_SERVER['SERVER_NAME'],
            'hostport' 				=> $_SERVER['SERVER_PORT'],
            'hostenv' 				=> $_SERVER["SERVER_SOFTWARE"],
            'hostsys' 				=> PHP_OS,
            'php_ext_time' 			=> ini_get("max_execution_time"),
			'hostlang'				=> $_SERVER['HTTP_ACCEPT_LANGUAGE'],
            'php_ext_type' 			=> php_sapi_name(),
            'hosttime' 				=> date("Y年n月j日 H:i:s"),
            'host_ext_time' 		=> $arRuntime[0],
            'php_ext_time' 			=> ini_get("max_execution_time"),
            'php_ext_time' 			=> ini_get("max_execution_time"),
            'php_ext_time' 			=> ini_get("max_execution_time"),
            'php_upload' 			=> ini_get('upload_max_filesize'),
            'sys_space' 			=> round((@disk_free_space(".") / (1024 * 1024)),2).'M',
            'register_globals' 		=> get_cfg_var("register_globals")=="1" ? "ON" : "OFF",
            'magic_quotes_gpc' 		=> (1 === get_magic_quotes_gpc()) ? 'YES' : 'NO',
            'magic_quotes_runtime' 	=> (1 === get_magic_quotes_runtime())?'YES':'NO',
		);
		$this->assign('info',$info);
		$hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
		$this->assign('minfo',$minfo);
		$this->display();
	}
	public function menu()
	{
		$code 		= trim(I('get.code'));
		$code 		= str_replace('-', '/', $code);
		$code		= !empty($code) ? $code : 'Config/group';
		$LeftMenu 	= $this->getMenus($code,true);
		$catmenu	= C('CAT_BIND_MENU');
		if (!empty($catmenu)){
			foreach ($catmenu as $k=>$v){
				$paths		= explode('-', $v);
				if ($code == $paths[0]){
					$this->menuid		= $k;
					$this->getCatMenu($code,$paths[1]);
				}
			}
		}
		$this->assign('LeftMenu',!empty($LeftMenu['child']) ? $LeftMenu['child'] : array());
		$menus			= $this->fetch('admin-left');
		$this->success($menus);
	}
	protected function getCatMenu($code='',$root=0){
		//获取动态分类
		$cate_auth  = AuthGroupModel::getAuthCategories(UID); //获取当前用户所有的内容权限节点
		$cate_auth  = $cate_auth == null ? array() : $cate_auth;

		$cate       = M('Category')->where(array('status'=>1))->field('id,name,pid')->order('pid,sort desc')->select();
		//没有权限的分类则不显示
		if(!IS_ROOT){
			foreach ($cate as $key=>$value){
				if(!in_array($value['id'], $cate_auth)){
					unset($cate[$key]);
				}
			}
		}
		$cate           = list_to_tree($cate, 'id', 'pid', '_child',$root);
		//获取分类id
		$cate_id        =   I('param.cate_id');
		$this->cate_id  =   $cate_id;

		//是否展开分类
		$hide_cate = false;
		if(ACTION_NAME != 'recycle' && ACTION_NAME != 'draftbox' && ACTION_NAME != 'mydocument'){
			$hide_cate  =   true;
		}
		//生成每个分类的url
		foreach ($cate as $key=>&$value){
			$value['url']   =   $code.'?cate_id='.$value['id'];
			if($cate_id == $value['id'] && $hide_cate){
				$value['current'] = true;
			}else{
				$value['current'] = false;
			}
			if(!empty($value['_child'])){
				$is_child = false;
				foreach ($value['_child'] as $ka=>&$va){
					$va['url']      =   $code.'?cate_id='.$va['id'];
					if(!empty($va['_child'])){
						foreach ($va['_child'] as $k=>&$v){
							$v['url']   =   $code.'?cate_id='.$v['id'];
							$v['pid']   =   $va['id'];
							$is_child = $v['id'] == $cate_id ? true : false;
						}
					}
					//展开子分类的父分类
					if($va['id'] == $cate_id || $is_child){
						$is_child = false;
						if($hide_cate){
							$value['current']   =   true;
							$va['current']      =   true;
						}else{
							$value['current']   =   false;
							$va['current']      =   false;
						}
					}else{
						$va['current']      =   false;
					}
				}
			}
		}
		$this->assign('nodes',      $cate);
	}
}
?>