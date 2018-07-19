<?php
namespace Admin\Model;
use Think\Model;
/**
 * 用户模型
 */
class MemberModel extends Model {
	protected $_validate = array(
	array('nickname', 'require', '用户昵称不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_UPDATE),
	array('nickname', '1,16', '昵称长度为1-16个字符', self::EXISTS_VALIDATE, 'length'),
	array('nickname', '', '昵称被占用', self::EXISTS_VALIDATE, 'unique'), //用户名被占用
	);
	protected $_auto = array(
	array('reg_time', NOW_TIME, self::MODEL_INSERT),
	array('updatetime', NOW_TIME, self::MODEL_BOTH),
	);
	
	/**
	 * 获取用户信息
	 */
	public function info($id, $field = true){
		/* 获取品牌信息 */
		$map['uid'] = $id;
		return $this->field($field)->where($map)->find();
	}
	
	/**
	 * 更新用户信息
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
	 * 登录指定用户
	 * @param  integer $uid 用户ID
	 * @return boolean      ture-登录成功，false-登录失败
	 */
	public function login($uid){
		/* 检测是否在当前应用注册 */
		$user = $this->field(true)->find($uid);
		if(!$user) {
			$this->error = '用户不存在或已被禁用！'; //应用级别禁用
			return false;
		}

		//记录行为
		action_log('member', $uid, $uid);

		/* 登录用户 */
		$this->autoLogin($user);
		return true;
	}

	/**
	 * 注销当前用户
	 * @return void
	 */
	public function logout(){
		session('user_auth', null);
		session('user_auth_sign', null);
	}

	/**
	 * 自动登录用户
	 * @param  integer $user 用户信息数组
	 */
	private function autoLogin($user){
		/* 更新登录信息 */
		$data = array(
            'uid'             => $user['uid'],
            'login'           => array('exp', '`login`+1'),
            'last_login_time' => NOW_TIME,
            'last_login_ip'   => get_client_ip(1),
		);
		$this->save($data);

		/* 记录登录SESSION和COOKIES */
		$auth = array(
            'uid'             => $user['uid'],
            'username'        => $user['nickname'],
            'last_login_time' => $user['last_login_time'],
		);

		session('user_auth', $auth);
		session('user_auth_sign', data_auth_sign($auth));

	}

	public function getNickName($uid){
		return $this->where(array('uid'=>(int)$uid))->getField('nickname');
	}
	
	/**
	 * 获取分类树，指定分类则返回指定分类极其子分类，不指定则返回所有分类树
	 * @param  integer $id    分类ID
	 * @param  boolean $field 查询字段
	 * @return array          分类树
	 */
	public function getTree($id = 0, $field = true,$map){
		/* 获取当前分类信息 */
		if($id){
			$info = $this->info($id, $field);
			$id   = $info['id'];
		}
		/* 获取所有分类 */
		$list 		= $this->field($field)->where($map)->order('uid asc')->select();
		$list 		= list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_', $root = $id);
		/* 获取返回数据 */
		if(isset($info)){ //指定分类则返回当前分类极其子分类
			$info['_'] = $list;
		} else { //否则返回所有分类
			$info = $list;
		}
		return $info;
	}
	
	/**
	 * 获取指定分类子分类信息
	 * @param  string $cate 分类ID
	 * @return string       id列表
	 */
	public function getChildrenList($id,$field=array()){
		$field		= empty($field) ? array('id','name') : $field;
		$list		= $this->where("status='1' and  pid='$id'")->field($field)->select();
		return $list;
	}
	/**
	 * 获取指定分类子分类ID
	 * @param  string $cate 分类ID
	 * @return string       id列表
	 */
	public function getChildrenId($id){
		$field 		= 'uid as id,pid,nickname as name';
		$category 	= $this->getTree($id, $field);
		$ids 		= array('0');
		$category	= $id > 0 ? array($category) : $category;
		$back_ids	= $this->SelectSonCatId($category);
		$ids		= !empty($ids) ? array_merge($ids,$back_ids) : $ids;
		return implode(',', $ids);
	}
	function SelectSonCatId($list){
		$ids		= array();
		if (!empty($list)){
			foreach ($list as $key => $value) {
				array_push($ids,$value['id']);
				if (!empty($value['_'])){
					$back_ids	= $this->SelectSonCatId($value['_']);
					$ids		= array_merge($ids,$back_ids);
				}
			}
		}
		return $ids;
	}
}
