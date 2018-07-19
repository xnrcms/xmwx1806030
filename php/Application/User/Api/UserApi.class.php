<?php
namespace User\Api;
use User\Api\Api;
use User\Model\UcenterMemberModel;

class UserApi extends Api{
    /**
     * 构造方法，实例化操作模型
     */
    protected function _init(){
        $this->model = new UcenterMemberModel();
    }

    /**
     * 注册一个新用户
     * @param  string $username 用户名
     * @param  string $password 用户密码
     * @param  string $email    用户邮箱
     * @param  string $mobile   用户手机号码
     * @return integer          注册成功-用户信息，注册失败-错误编号
     */
    public function register($username, $password, $email='', $mobile = ''){
        return $this->model->register($username, $password, $email, $mobile);
    }

    /**
     * 第三方注册
     * @param  string $username 用户名
     * @param  string $password 用户密码
     * @param  string $openid   用户openid
     * @param  string $type     用户注册类型 1微信 2qq
     * @return integer          注册成功-用户信息，注册失败-错误编号
     */
    public function thirdRegister($username, $password, $token,$type){
        return $this->model->thirdRegister($username, $password, $token,$type);
    }

    /**
     * 用户登录认证
     * @param  string  $username 用户名
     * @param  string  $password 用户密码
     * @param  integer $type     用户名类型 （1-用户名，2-邮箱，3-手机，4-UID）
     * @return integer           登录成功-用户ID，登录失败-错误编号
     */
    public function login($username, $password, $type = 1){
        return $this->model->login($username, $password, $type);
    }

    /**
     * 获取用户信息
     * @param  string  $uid         用户ID或用户名
     * @param  boolean $is_username 是否使用用户名查询
     * @return array                用户信息
     */
    public function info($uid, $is_username = false){
        return $this->model->info($uid, $is_username);
    }

    /**
     * 检测用户名
     * @param  string  $field  用户名
     * @return integer         错误编号
     */
    public function checkUsername($username){
        return $this->model->checkField($username, 1);
    }

    /**
     * 检测邮箱
     * @param  string  $email  邮箱
     * @return integer         错误编号
     */
    public function checkEmail($email){
        return $this->model->checkField($email, 2);
    }

    /**
     * 检测手机
     * @param  string  $mobile  手机
     * @return integer         错误编号
     */
    public function checkMobile($mobile){
        return $this->model->checkField($mobile, 3);
    }

    /**
     * 更新用户信息
     * @param int $uid 用户id
     * @param string $password 密码，用来验证
     * @param array $data 修改的字段数组
     * @return true 修改成功，false 修改失败
     * @author huajie <banhuajie@163.com>
     */
    public function updateInfo($uid, $password, $data,$ispass=true){
        if($this->model->updateUserFields($uid, $password, $data,$ispass) !== false){
            $return['status'] = true;
        }else{
            $return['status'] = false;
            $error				= $this->model->getError();
            $return['info'] 	= is_numeric($error) ? $this->showRegError($error): $error;
            $return['error'] 	= $error;
        }
        return $return;
    }
	public function showRegError($code = 0){
		$lang	= L('USER_LANG');
		switch ($code) {
			case -1:  $error = $lang['100100']; break;
			case -2:  $error = $lang['100101']; break;
			case -3:  $error = $lang['100102']; break;
			case -4:  $error = $lang['100103']; break;
			case -5:  $error = $lang['100104']; break;
			case -6:  $error = $lang['100105']; break;
			case -7:  $error = $lang['100106']; break;
			case -8:  $error = $lang['100107']; break;
			case -9:  $error = $lang['100108']; break;
			case -10: $error = $lang['100109']; break;
			case -11: $error = $lang['100110']; break;
			case -12: $error = $lang['100111']; break;
			case -13: $error = $lang['100112']; break;
			default:  $error = L('UNKNOWN_ERROR');
		}
		return $error;
	}
}
