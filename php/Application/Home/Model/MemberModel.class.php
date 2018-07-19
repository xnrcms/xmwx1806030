<?php
namespace Home\Model;
use Think\Model;
use User\Api\UserApi;
/**
 * 用户模型
 *@author 王远庆
 */
class MemberModel extends Model{
	/* 用户模型自动完成 */
	protected $_auto = array(
	array("login", 0, self::MODEL_INSERT),
	array("reg_ip", "get_client_ip", self::MODEL_INSERT, "function", 1),
	array("reg_time", NOW_TIME, self::MODEL_INSERT),
	array("last_login_ip", 0, self::MODEL_INSERT),
	array("last_login_time", 0, self::MODEL_INSERT),
	array("status", 1, self::MODEL_INSERT),
	);

	public function info($map,$field=true){
		return $this->field($field)->where($map)->find();
	}
	/**
	 * 登录指定用户
	 * @author 王远庆
	 * @param  integer $uid 用户ID
	 * @return boolean      ture-登录成功，false-登录失败
	 */
	public function login($uid){
		/* 检测是否在当前应用注册 */
		$user = $this->field(true)->find($uid);
		if(!$user){ //未注册
			/* 在当前应用中注册用户 */
			$Api 		= new UserApi();
			$info 		= $Api->info($uid);
			//6位数字邀请码
			$code = substr($uid.rand(10000,99999), 0, 6);
			$user 		= $this->create(array('uid'=>$uid, 'code'=>$code));
			if(!$this->add($user)){
				$this->error = "注册失败！";return false;
			}
		}
		$this->autoLogin($user);
		/* 登录历史 */
		history($uid);
		//记录行为
		action_log("member", $uid, $uid);
		return $user;
	}
	/**
	 * 数据更新
	 * @author 王远庆
	 */
	public function update($data)
	{
		$data = $this->create($data);
		if(!$data) return false;
		/* 添加或更新数据 */
		if(empty($data['uid'])){
			$uid = $this->add();
			if(!$uid){
				$this->error = '新增失败！';
				return false;
			}
		}else{
			$res = $this->save();
			if(false === $res){
				$this->error = '更新失败！';
				return false;
			}
		}
		$data['uid']	= $data['uid'] >0 ? $data['uid'] : $uid;
		return $data;
	}
	/**
	 * 自动登录用户
	 * @param  integer $user 用户信息数组
	 */
	private function autoLogin($user){
		/* 更新登录信息 */
		$data = array(
            "uid"             => $user["uid"],
            "login"           => array("exp", "`login`+1"),
            "last_login_time" => NOW_TIME,
            "last_login_ip"   => get_client_ip(1),
		);
		$this->save($data);
	}
}
?>