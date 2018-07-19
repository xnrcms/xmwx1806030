<?php
namespace Home\Controller;
use Think\Controller;
/**
 * 后台首页控制器
 */
class BaseController extends Controller {
	
	/**
	 * 后台控制器初始化
	 */
	public function _initialize(){
		//店铺id
		session('shop_id_string',I('get.shop_id','','trim'));
		// 获取当前用户ID
		if(!session('uid')){
			$this->AuthLogin();
		}
		define('UID',session('uid'));
		if(!UID ){
			if (IS_AJAX){
				$this->ajaxReturn(array('Code' =>'1','Msg'=>'您还没有登录,请先登录!'));
			}else{
				header("Location:".U('User/login'));exit;
			}
		}
	}
	
	//执行自动登录
	protected function AuthLogin(){
		$cookie_username	= cookie(md5('home_username'.C('DATA_AUTH_KEY')));
		if($cookie_username){
			$username		= FauthCode($cookie_username,'DECODE');
			$username 		= safe_replace($username);//过滤
			/* 登录 */
			$user 			= M('user')->where(array('phone'=>$username,'type'=>1))->find();
			if(0 < $user['id']){ //登录成功
				session('uid',$user['id']);
				cookie(md5('home_username'.C('DATA_AUTH_KEY')),FauthCode($user['phone'],'ENCODE'),2592000); // 指定cookie保存30天时间
			}else{
				//session('[destroy]');
				//cookie(null);
			}
		}else{
			//session('[destroy]');
			//cookie(null);
		}
	}
	
}