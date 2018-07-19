<?php
namespace Admin\Controller;
use User\Api\UserApi;
/**
 * 后台首页控制器
 */
class PublicController extends \Think\Controller {
	/**
	 * 后台用户登录
	 */
	public function login($username = null, $password = null, $verify = null,$remember=0){
		if(IS_POST){
			$is_verify	= C('ADMIN_IS_VERIFY');
			/* 检测验证码 TODO: */
			if(!check_verify($verify) && $is_verify == 1){
				$this->error('验证码输入错误！');
			}
			/* 调用UC登录接口登录 */
			$User = new UserApi;
			$uid = $User->login($username, $password);
			if(0 < $uid){ //UC登录成功
				/* 登录用户 */
				$Member = D('Member');
				if($Member->login($uid)){ //登录用户
					if ($remember == 1){
						//保存session信息
						cookie(md5('admin_username'.C('DATA_AUTH_KEY')),FauthCode($username,'ENCODE'),2592000); // 指定cookie保存30天时间
						cookie(md5('admin_password'.C('DATA_AUTH_KEY')),FauthCode($password,'ENCODE'),2592000); // 指定cookie保存30天时间
					}
					//TODO:跳转到登录前页面
					$this->success('登录成功！', U('Index/index'));
				} else {
					session('[destroy]');
					cookie(null);
					$this->error($Member->getError());
				}

			} else { //登录失败
				switch($uid) {
					case -1: $error = '用户不存在或被禁用！'; break; //系统级别禁用
					case -2: $error = '密码错误！'; break;
					default: $error = '未知错误！'; break; // 0-接口参数错误（调试阶段使用）
				}
				session('[destroy]');
				cookie(null);
				$this->error($error);
			}
		} else {
			if(is_login()){
				$this->redirect('Index/index');
			}else{
				/* 读取数据库中的配置 */
				$config	=	S('DB_CONFIG_DATA');
				if(!$config){
					$config	=	D('Config')->lists();
					S('DB_CONFIG_DATA',$config);
				}
				C($config); //添加配置
				$is_verify	= C('ADMIN_IS_VERIFY');
				$this->assign('is_verify', $is_verify);
				$this->display();
			}
		}
	}
	/* 退出登录 */
	public function logout(){
		if(is_login()){
			D('Member')->logout();
			session('[destroy]');
			cookie(null);
			$this->success('退出成功！', U('login'));
		} else {
			$this->redirect('login');
		}
	}
	/* 验证码*/
	public function verify(){
		$config['imageH']	= '40';
		$config['imageW']	= '204';
		$config['length']	= 5;
		$config['fontSize']	= 21;
		$config['codeSet']	= '0123456789';
		$verify = new \Think\Verify($config);
		$verify->entry(1);
	}
}
?>