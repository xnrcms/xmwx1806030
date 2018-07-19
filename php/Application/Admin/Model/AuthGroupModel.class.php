<?php
namespace Admin\Model;
use Think\Model;
/**
 * 用户组模型类
 * Class AuthGroupModel
 * @author 朱亚杰 <zhuyajie@topthink.net>
 */
class AuthGroupModel extends Model {
	const TYPE_ADMIN                = 1;                   // 管理员用户组类型标识
	const MEMBER                    = 'member';
	const UCENTER_MEMBER            = 'ucenter_member';
	const AUTH_GROUP_ACCESS         = 'auth_group_access'; // 关系表表名
	const AUTH_EXTEND               = 'auth_extend';       // 动态权限扩展信息表
	const AUTH_GROUP                = 'auth_group';        // 用户组表名
	const AUTH_EXTEND_CATEGORY_TYPE = 1;              // 分类权限标识
	const AUTH_EXTEND_MODEL_TYPE    = 2; //分类权限标识
	
	/*
	 * ===========自动验证定义说明==============
	 * Field		必填-需要验证的表单字段名称，这个字段不一定是数据库字段，也可以是表单的一些辅助字段，例如确认密码和验证码等等。
	 * 					  有个别验证规则和字段无关的情况下，验证字段是可以随意设置的，例如expire有效期规则是和表单字段无关的。如果定义了字段映射的话，这里的验证字段名称应该是实际的数据表字段而不是表单字段
	 * Rule			必填-要进行验证的规则，需要结合附加规则，如果在使用正则验证的附加规则情况下，系统还内置了一些常用正则验证的规则，可以直接作为验证规则使用，
	 * 					  包括：require 字段必须、email 邮箱、url URL地址、currency 货币、number 数字。
	 * ErrorMsg		必填-用于验证失败后的提示信息定义
	 * CheckTime	不必-验证条件
	 * 				self::EXISTS_VALIDATE 或者0	:存在字段就验证(默认)
	 * 				self::MUST_VALIDATE   或者1 	:必须验证
	 * 				self::VALUE_VALIDATE  或者2 	:值不为空的时候验证
	 * Rule2		不必-配合验证规则使用，包括下面一些规则
	 * 				regex		:正则验证，定义的验证规则是一个正则表达式(默认)
	 * 				function	:函数验证，定义的验证规则是一个函数名
	 * 				callback	:回调方法验证，定义的验证规则是当前模型类的一个方法
	 * 				confirm 	:验证表单中的两个字段是否相同，定义的验证规则是一个字段名
	 * 				equal 		:验证是否等于某个值，该值由前面的验证规则定义
	 * 				notequal 	:验证是否不等于某个值，该值由前面的验证规则定义（3.1.2版本新增）
	 * 				in 			:验证是否在某个范围内，定义的验证规则可以是一个数组或者逗号分割的字符串
	 * 				notin 		:验证是否不在某个范围内，定义的验证规则可以是一个数组或者逗号分割的字符串（3.1.2版本新增）
	 * 				length 		:验证长度，定义的验证规则可以是一个数字（表示固定长度）或者数字范围（例如3,12 表示长度从3到12的范围）
	 * 				between 	:验证范围，定义的验证规则表示范围，可以使用字符串或者数组，例如1,31或者array(1,31)
	 * 				notbetween 	:验证不在某个范围，定义的验证规则表示范围，可以使用字符串或者数组（3.1.2版本新增）
	 * 				unique 		:验证是否唯一，系统会根据字段目前的值查询数据库来判断是否存在相同的值，当表单数据中包含主键字段时unique不可用于判断主键字段本身
	 * DoneTime		不必-完成时间
	 * 				self::MODEL_INSERT或者1	:新增数据的时候验证(默认)
	 * 				self::MODEL_UPDATE或者2	:更新数据的时候验证
	 * 				self::MODEL_BOTH或者3	:全部情况下验证(默认)
	 * */
	protected $_validate = array(
	array('title','require', '必须设置用户组标题', Model::MUST_VALIDATE ,'regex',Model::MODEL_BOTH),
	array('description','0,80', '描述最多80字符', Model::VALUE_VALIDATE , 'length'  ,Model::MODEL_BOTH ),
	//array('Field','Rule','ErrorMsg','CheckTime','Rule2','DoneTime'),
	);
	/*
	 * ===========自动完成定义说明==============
	 * Field		必填-需要操作的字段
	 * Rule			必填-需要处理的规则，配合附加规则完成
	 * DoneTime		不必-完成时间
	 * 				self::MODEL_INSERT 或者1	:新增数据的时候处理(默认)
	 * 				self::MODEL_UPDATE 或者2	:更新数据的时候处理
	 * 				self::MODEL_BOTH   或者3	:所有情况都进行处理
	 * Rule2		不必-附加规则
	 * 				function:使用函数，表示填充的内容是一个函数名
	 * 				callback:回调方法 ，表示填充的内容是一个当前模型的方法
	 * 				field	:用其它字段填充，表示填充的内容是一个其他字段的值
	 * 				string	:字符串（默认方式）
	 * 				ignore	:为空则忽略（3.1.2新增）
	 * */
	protected $_auto = array(
	array('create_time', NOW_TIME, self::MODEL_INSERT),
	array('update_time', NOW_TIME, self::MODEL_BOTH),
	array('status', '1', self::MODEL_INSERT, 'string'),
	//array('Field',Rule,DoneTime,Rule2)
	);
	//更新数据
	public function update($updata = array())
	{
		$data 		= $this->create($updata);
		if(!$data) return false;
		/* 添加或更新数据 */
		if(empty($data['id'])){
			$res = $this->add();
			if(!$res) {$this->error = '新增失败！';return false;}
		}else{
			$res = $this->save();
			if(false === $res) {$this->error = '更新失败！';return false;}
		}
		$data['ac']	= $data['id'] >0 ? 0 : 1;//添加还是编辑
		$data['id']	= $data['id'] >0 ? $data['id'] : $res;
		return $data;
	}
	/**
	 * 返回用户组列表
	 * 默认返回正常状态的管理员用户组列表
	 * @param array $where   查询条件,供where()方法使用
	 *
	 * @author 朱亚杰 <zhuyajie@topthink.net>
	 */
	public function getGroups($where=array()){
		$map = array('status'=>1,'type'=>self::TYPE_ADMIN,'module'=>'admin');
		$map = array_merge($map,$where);
		return $this->where($map)->select();
	}

	/**
	 * 把用户添加到用户组,支持批量添加用户到用户组
	 * @author 朱亚杰 <zhuyajie@topthink.net>
	 *
	 * 示例: 把uid=1的用户添加到group_id为1,2的组 `AuthGroupModel->addToGroup(1,'1,2');`
	 */
	public function addToGroup($uid,$gid){
		$uid = is_array($uid)?implode(',',$uid):trim($uid,',');
		$gid = is_array($gid)?$gid:explode( ',',trim($gid,',') );

		$Access = M(self::AUTH_GROUP_ACCESS);
		if( isset($_REQUEST['batch']) ){
			//为单个用户批量添加用户组时,先删除旧数据
			$del = $Access->where( array('uid'=>array('in',$uid)) )->delete();
		}

		$uid_arr = explode(',',$uid);
		$uid_arr = array_diff($uid_arr,array(C('USER_ADMINISTRATOR')));
		$add = array();
		if( $del!==false ){
			foreach ($uid_arr as $u){
				foreach ($gid as $g){
					if( is_numeric($u) && is_numeric($g) ){
						$add[] = array('group_id'=>$g,'uid'=>$u);
					}
				}
			}
			$Access->addAll($add);
		}
		if ($Access->getDbError()) {
			if( count($uid_arr)==1 && count($gid)==1 ){
				//单个添加时定制错误提示
				$this->error = "不能重复添加";
			}
			return false;
		}else{
			return true;
		}
	}

	/**
	 * 返回用户所属用户组信息
	 * @param  int    $uid 用户id
	 * @return array  用户所属的用户组 array(
	 *                                         array('uid'=>'用户id','group_id'=>'用户组id','title'=>'用户组名称','rules'=>'用户组拥有的规则id,多个,号隔开'),
	 *                                         ...)
	 */
	static public function getUserGroup($uid){
		static $groups = array();
		if (isset($groups[$uid]))
		return $groups[$uid];
		$prefix = C('DB_PREFIX');
		$user_groups = M()
		->field('uid,group_id,title,description,rules')
		->table($prefix.self::AUTH_GROUP_ACCESS.' a')
		->join ($prefix.self::AUTH_GROUP." g on a.group_id=g.id")
		->where("a.uid='$uid' and g.status='1'")
		->select();
		$groups[$uid]=$user_groups?$user_groups:array();
		return $groups[$uid];
	}

	/**
	 * 返回用户拥有管理权限的扩展数据id列表
	 *
	 * @param int     $uid  用户id
	 * @param int     $type 扩展数据标识
	 * @param int     $session  结果缓存标识
	 * @return array
	 *
	 *  array(2,4,8,13)
	 *
	 * @author 朱亚杰 <xcoolcc@gmail.com>
	 */
	static public function getAuthExtend($uid,$type,$session){
		if ( !$type ) {
			return false;
		}
		if ( $session ) {
			$result = session($session);
		}
		if ( $uid == UID && !empty($result) ) {
			return $result;
		}
		$prefix = C('DB_PREFIX');
		$result = M()
		->table($prefix.self::AUTH_GROUP_ACCESS.' g')
		->join($prefix.self::AUTH_EXTEND.' c on g.group_id=c.group_id')
		->where("g.uid='$uid' and c.type='$type' and !isnull(extend_id)")
		->getfield('extend_id',true);
		if ( $uid == UID && $session ) {
			session($session,$result);
		}
		return $result;
	}

	/**
	 * 返回用户拥有管理权限的分类id列表
	 *
	 * @param int     $uid  用户id
	 * @return array
	 *
	 *  array(2,4,8,13)
	 *
	 * @author 朱亚杰 <zhuyajie@topthink.net>
	 */
	static public function getAuthCategories($uid){
		return self::getAuthExtend($uid,self::AUTH_EXTEND_CATEGORY_TYPE,'AUTH_CATEGORY');
	}



	/**
	 * 获取用户组授权的扩展信息数据
	 *
	 * @param int     $gid  用户组id
	 * @return array
	 *
	 *  array(2,4,8,13)
	 *
	 * @author 朱亚杰 <xcoolcc@gmail.com>
	 */
	static public function getExtendOfGroup($gid,$type){
		if ( !is_numeric($type) ) {
			return false;
		}
		return M(self::AUTH_EXTEND)->where( array('group_id'=>$gid,'type'=>$type) )->getfield('extend_id',true);
	}

	/**
	 * 获取用户组授权的分类id列表
	 *
	 * @param int     $gid  用户组id
	 * @return array
	 *
	 *  array(2,4,8,13)
	 *
	 * @author 朱亚杰 <zhuyajie@topthink.net>
	 */
	static public function getCategoryOfGroup($gid){
		return self::getExtendOfGroup($gid,self::AUTH_EXTEND_CATEGORY_TYPE);
	}


	/**
	 * 批量设置用户组可管理的扩展权限数据
	 *
	 * @param int|string|array $gid   用户组id
	 * @param int|string|array $cid   分类id
	 *
	 * @author 朱亚杰 <xcoolcc@gmail.com>
	 */
	static public function addToExtend($gid,$cid,$type){
		$gid = is_array($gid)?implode(',',$gid):trim($gid,',');
		$cid = is_array($cid)?$cid:explode( ',',trim($cid,',') );

		$Access = M(self::AUTH_EXTEND);
		$del = $Access->where( array('group_id'=>array('in',$gid),'type'=>$type) )->delete();

		$gid = explode(',',$gid);
		$add = array();
		if( $del!==false ){
			foreach ($gid as $g){
				foreach ($cid as $c){
					if( is_numeric($g) && is_numeric($c) ){
						$add[] = array('group_id'=>$g,'extend_id'=>$c,'type'=>$type);
					}
				}
			}
			$Access->addAll($add);
		}
		if ($Access->getDbError()) {
			return false;
		}else{
			return true;
		}
	}

	/**
	 * 批量设置用户组可管理的分类
	 *
	 * @param int|string|array $gid   用户组id
	 * @param int|string|array $cid   分类id
	 *
	 * @author 朱亚杰 <zhuyajie@topthink.net>
	 */
	static public function addToCategory($gid,$cid){
		return self::addToExtend($gid,$cid,self::AUTH_EXTEND_CATEGORY_TYPE);
	}


	/**
	 * 将用户从用户组中移除
	 * @param int|string|array $gid   用户组id
	 * @param int|string|array $cid   分类id
	 * @author 朱亚杰 <xcoolcc@gmail.com>
	 */
	public function removeFromGroup($uid,$gid){
		return M(self::AUTH_GROUP_ACCESS)->where( array( 'uid'=>$uid,'group_id'=>$gid) )->delete();
	}

	/**
	 * 获取某个用户组的用户列表
	 *
	 * @param int $group_id   用户组id
	 *
	 * @author 朱亚杰 <zhuyajie@topthink.net>
	 */
	static public function memberInGroup($group_id){
		$prefix   = C('DB_PREFIX');
		$l_table  = $prefix.self::MEMBER;
		$r_table  = $prefix.self::AUTH_GROUP_ACCESS;
		$r_table2 = $prefix.self::UCENTER_MEMBER;
		$list     = M() ->field('m.uid,u.username,m.last_login_time,m.last_login_ip,m.status')
		->table($l_table.' m')
		->join($r_table.' a ON m.uid=a.uid')
		->join($r_table2.' u ON m.uid=u.id')
		->where(array('a.group_id'=>$group_id))
		->select();
		return $list;
	}

	/**
	 * 检查id是否全部存在
	 * @param array|string $gid  用户组id列表
	 * @author 朱亚杰 <zhuyajie@topthink.net>
	 */
	public function checkId($modelname,$mid,$msg = '以下id不存在:'){
		if(is_array($mid)){
			$count = count($mid);
			$ids   = implode(',',$mid);
		}else{
			$mid   = explode(',',$mid);
			$count = count($mid);
			$ids   = $mid;
		}

		$s = M($modelname)->where(array('id'=>array('IN',$ids)))->getField('id',true);
		if(count($s)===$count){
			return true;
		}else{
			$diff = implode(',',array_diff($mid,$s));
			$this->error = $msg.$diff;
			return false;
		}
	}

	/**
	 * 检查用户组是否全部存在
	 * @param array|string $gid  用户组id列表
	 * @author 朱亚杰 <zhuyajie@topthink.net>
	 */
	public function checkGroupId($gid){
		return $this->checkId('AuthGroup',$gid, '以下用户组id不存在:');
	}

	/**
	 * 检查分类是否全部存在
	 * @param array|string $cid  栏目分类id列表
	 * @author 朱亚杰 <zhuyajie@topthink.net>
	 */
	public function checkCategoryId($cid){
		return $this->checkId('Category',$cid, '以下分类id不存在:');
	}


}

