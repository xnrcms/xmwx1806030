<?php
namespace Admin\Controller;
/**
 * 后台用户控制器
 */
class AppUserController extends AdminController {
	protected $parentid	= 0;
	protected $groupid	= 0;
	/**
	 * 用户列表
	 */
public function index(){
		$limit						= 20;

		//获取数据
		$MainTab					= 'user';
		$MainAlias					= 'main';
		$MainField 					= array();
		$MainField 					= array();

		//主表模型
		$MainModel 					= D($MainTab)->alias($MainAlias);

		/*
		 * 灵活定义关联查询
		 * Ralias 	关联表别名
		 * Ron    	关联条件
		 * Rfield	关联查询字段，
		 * */
		$RelationTab				= array(
			//'member'=>array('Ralias'=>'me','Ron'=>'me ON me.uid=main.uid','Rfield'=>array('uid as uuid','nickname')),
		);
		$RelationTab				= $this->getRelationTab($RelationTab);
		$tables	  					= $RelationTab['tables'];
		$RelationFields				= $RelationTab['fields'];
		$model						= !empty($tables) ? $MainModel->join ( $tables ,'LEFT') : $MainModel;

		//检索条件
		$map 						= array();
		$map['type'] 				= 1;
		//时间区间检索
		$search_time				= time_between('create_time',$MainAlias);
		//用户昵称检索
		$nickname 					= I('nickname','');
		if(!empty($nickname)){
			$map['nickname'] 		= array('like', '%'.$nickname.'%');
		}
		//手机号码检索
		$phone 						= I('phone','');
		if(!empty($phone)){
			$map['phone'] 			= array('like', '%'.$phone.'%');
		}
		//状态检索
		$status 				= intval(I('get.find_status',0));
		if(!empty($status) && $status > 0){
			$map['status'] 		= $status;
		}

		$map						= array_merge($map,$search_time);
		//排序
		$_order = I('get.order', '', 'trim');
		$sort	= I('get.sort', '', 'trim');
		$sort 	= empty($sort) ? 'desc' : $sort;
		switch ($_order) {
			case 'reg_time':
				$order = $MainAlias.'.create_time '.$sort;
				break;
			case 'consumption_money':
				$order = $MainAlias.'.consumption_money '.$sort;
				break;
			default:
				$order = $MainAlias.'.id desc';
				break;
		}

		//检索字段
		$fields						= (empty($MainField) ? $this->get_fields_string($MainModel->getDbFields(),$MainAlias).',' : $this->get_fields_string($MainField,$MainAlias).',') . $RelationFields;
		$fields						= trim($fields,',');

		//列表数据
		$sex						= array('--','男','女');
		$list 						= $this->getLists($model,$map,$order,$fields,1,$limit,true);
		
		//p($list);die;
		
		
		if (!empty($list)){
			foreach ($list as $k=>$v){
				//数据格式化
				$list[$k]['sex']					= $sex[$v['sex']];
				$list[$k]['create_time']			= $v['create_time'] > 0 ? date('Y-m-d H:i:s',$v['create_time']) : '--';
			}
		}
		$this->assign('_list', $list);
		//操作菜单,可以根据需要固定$menuid,$menuid为Menu表中的ID
		$menuid								= $this->menuid;
		$SonMenu							= $this->getSonMenu($menuid);
		$this->assign('ListTopNav', 		!empty($SonMenu['TOPMENU']) ? $SonMenu['TOPMENU'] : array());
		$this->assign('ListRightNav', 		!empty($SonMenu['RIGHTMENU']) ? $SonMenu['RIGHTMENU'] : array());

		//代码扩展
		//$this->extends_param				.= $this->extends_param;
		$this->extends_param				= '';
		//.........
		//代码扩展
		
		$this->NavTitle = '会员管理';
		$this->assign('SmallNav', 			array('会员管理','会员列表'));
		//记录当前列表页的cookie
		if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf')) Cookie('__forward__',$_SERVER['REQUEST_URI']);
		
		$this->display();
	}
	
	/**
	 * 数据
	 */
	public function addedit($id = 0){
		//数据提交
		if (IS_POST){
			//id
			$this->update();
		}else{
			if($id){
				//页面数据
				$info 			= M('user')->field(getField(C('CURRENT_LANGUAGE'), M('user')->getDbFields()))->find($id);
				if(false === $info){
					$this->error('获取信息错误');
				}
				$this->NavTitle = '编辑';
				//表单数据
				$FormData						= $this->CustomerForm(0);
				$FormData[0] 					= array('fieldName'=>L('LOGIN_ACCOUNT'),'fieldValue'=>'username','fieldType'=>'show','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder="'.L('LOGIN_ACCOUNT').'" autocomplete="off" disableautocomplete');
			}else{
				//页面数据
				$info 							= array();
				$this->NavTitle = '新增';
				//表单数据
				$FormData						= $this->CustomerForm(0);
			}
			$this->assign('info',$info);
			$this->assign('FormData',       $FormData);
			$this->display();
		}
		
	}

	/**
	 * 删除数据
	 */
	public function del(){
		$ids			= I('request.ids');
		if ( empty($ids) ) { $this->error('请选择要操作的数据!');}
		$ids 			= is_array($ids) ? $ids : array(intval($ids));
		$ids			= array_unique($ids);
		$map 			= array('id' => array('in', $ids) );
		if(D('User')->where($map)->delete()){
			//记录行为
			action_log('config',$ids,UID);
			//数据返回
			$this->success('删除成功',Cookie('__forward__'));
		} else {
			$this->error('删除失败！');
		}
	}

	//提交表单
	protected function update(){
		if(IS_POST){
			$Models 		= D('User');
			//数据整理
			//.......
			//数据整理
			$res 			= $Models->update();
			if(false !== $res){
				//记录行为
				//action_log('article',$data['id'],UID);
				//数据返回
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
	 * 	账号信息
	 */
	public function userView($id = 0) {
		//页面数据
		//不同语言字段转换
		$info 	= M('user')->field(getField(C('CURRENT_LANGUAGE'), M('user')->getDbFields()))->find($id);
		
		if (false === $info) {
			$this->error('获取信息错误');
		}
		if($info['sex'] == 1){
			$info['sex'] = L('MALE');
		}elseif($info['sex'] == 2){
			$info['sex'] = L('FEMALE');
		}else{
			$info['sex'] = L('CONFIDENTIALITY');
		}
		$this->assign('info', $info);
		
		//表单数据
		$FormData = $this->CustomerForm(3);
		if($info['type'] == 1){
			unset($FormData[2]);
			unset($FormData[11]);
		}elseif($info['type'] == 2){
			unset($FormData[3]);
			unset($FormData[11]);
		}elseif($info['type'] == 3){
			unset($FormData[2]);
		}
		array_pop($FormData);
		$this->assign('FormData', $FormData);
		
		$this->NavTitle = '账号审核';
		$this->display('userView');
	}
	
	/**
	 * 	账号审核
	 */
	public function userCheck($id = 0) {
		//数据提交
		if (IS_POST){
			$Models = M('user');
			//数据整理
			//.......
			$id 	= I('post.id',0,'intval');
			$check_status = I('post.check_status',0,'intval');
			//数据整理
			$user = $Models->where(array('id'=>$id))->find();
			if($user['check_status'] == 2){
				$this->error('已审核,请不要重复审核！');
			}
			$row = array();
			$row['check_status'] = $check_status;
			$res = $Models->where(array('id'=>$id))->save($row);
			if (false !== $res) {
				//数据返回
				$this->success('审核成功', Cookie('__forward__'));
			} else {
				$error = $Models->getError();
				$this->error(empty($error) ? '未知错误！' : $error);
			}
		}else{
			//页面数据
			//不同语言字段转换
			$info 	= M('user')->find($id);
			 
			if (false === $info) {
				$this->error('获取信息错误');
			}
			$this->assign('info', $info);
			 
			//表单数据
			$FormData = $this->CustomerForm(3);
			$this->assign('FormData', $FormData);
			 
			$this->NavTitle = '账号审核';
			$this->display('userCheck');
		}
	}

	/*
	 * fieldName	字段名称
	 * fieldValue	字段值
	 * fieldType	字段类型[
	 * 				show		:纯展示
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
		
		//账号添加
		$FormData[0] = array(
			array('fieldName'=>L('LOGIN_ACCOUNT'),'fieldValue'=>'username','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="'.L('LOGIN_ACCOUNT').'" autocomplete="off" disableautocomplete'),
			array('fieldName'=>L('LOGIN_PASSWORD'),'fieldValue'=>'password','fieldType'=>'password','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="'.L('LOGIN_PASSWORD').'" autocomplete="off" disableautocomplete'),
			array('fieldName'=>L('CONFIRM_PASSWORD'),'fieldValue'=>'repassword','fieldType'=>'password','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="'.L('CONFIRM_PASSWORD').'" autocomplete="off" disableautocomplete'),
			array('fieldName'=>L('SEX'),'fieldValue'=>'sex','fieldType'=>'radio','isMust'=>1,'fieldData'=>array(1=>L('MALE'),2=>L('FEMALE')),'attrExtend'=>'placeholder="'.L('sex').'"'),
			array('fieldName'=>L('AGE'),'fieldValue'=>'age','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="'.L('AGE').'"'),
			array('fieldName'=>L('CAREER'),'fieldValue'=>'occupation','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="'.L('CAREER').'"'),
			array('fieldName'=>L('TRUE_NAME'),'fieldValue'=>'true_name','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="'.L('TRUE_NAME').'"'),
			array('fieldName'=>L('NATIONALITY'),'fieldValue'=>'nationality','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="'.L('NATIONALITY').'"'),
			array('fieldName'=>L('PHONE'),'fieldValue'=>'phone','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="'.L('PHONE').'"'),
			array('fieldName'=>L('EMAIL'),'fieldValue'=>'email','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="'.L('EMAIL').'"'),
			array('fieldName'=>L('ID_CARD'),'fieldValue'=>'idcard','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="'.L('ID_CARD').'"'),
			array('fieldName'=>L('RESIDENTIAL_ADDRESS'),'fieldValue'=>'address','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="'.L('RESIDENTIAL_ADDRESS').'"'),
			array('fieldName'=>L('HIDE_THE_DOMAIN'),'fieldValue'=>array('id'),'fieldType'=>'hidden','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder=""'),
		);
		//修改密码
		$FormData[1] = array(
			array('fieldName'=>L('LOGIN_ACCOUNT'),'fieldValue'=>'username','fieldType'=>'show','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder="'.L('LOGIN_ACCOUNT').'" autocomplete="off" disableautocomplete'),
			array('fieldName'=>L('LOGIN_PASSWORD'),'fieldValue'=>'password','fieldType'=>'password','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder="'.L('LOGIN_PASSWORD').'" autocomplete="off" disableautocomplete'),
			array('fieldName'=>L('CONFIRM_PASSWORD'),'fieldValue'=>'repassword','fieldType'=>'password','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder="'.L('CONFIRM_PASSWORD').'" autocomplete="off" disableautocomplete'),
			array('fieldName'=>L('HIDE_THE_DOMAIN'),'fieldValue'=>array('id'),'fieldType'=>'hidden','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder=""'),
		);
		
		
		$FormData[3] = array(
			array('fieldName' => '账号', 'fieldValue' => 'phone', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => ''),
			array('fieldName' => '邀请码', 'fieldValue' => 'code', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => ''),
			array('fieldName' => '开户人', 'fieldValue' => 'account_person', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => ''),
			array('fieldName' => '银行账号', 'fieldValue' => 'bank_account', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => ''),
			array('fieldName' => '证件类型', 'fieldValue' => 'certificates_type', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => ''),
			array('fieldName' => '证件姓名', 'fieldValue' => 'document_name', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => ''),
			array('fieldName' => '证件号','fieldValue'=>'certificates_number','fieldType'=>'show','isMust'=>0,'fieldData'=>array(),'attrExtend'=>''),
			array('fieldName' => '身份证正面照片', 'fieldValue' => 'id_front_img', 'fieldType' => 'images_show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'data-table="avatar" data-field="pic" data-size=""'),
			array('fieldName' => '身份证反面照片', 'fieldValue' => 'id_back_img', 'fieldType' => 'images_show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'data-table="avatar" data-field="pic" data-size=""'),
			array('fieldName' => '营业执照', 'fieldValue' => 'license_img', 'fieldType' => 'images_show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'data-table="avatar" data-field="pic" data-size=""'),
			array('fieldName' => '审核','fieldValue'=>'check_status','fieldType'=>'radio','isMust'=>0,'fieldData'=>array('2'=>'通过','3'=>'不通过'),'attrExtend'=>''),
			array('fieldName'=>'隐藏域','fieldValue'=>array('id'),'fieldType'=>'hidden','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder=""'),
		);
		return $FormData[$index];
	}

	/**
	 * 获取用户注册错误信息
	 * @param  integer $code 错误编码
	 * @return string        错误信息
	 */
	private function showRegError($code = 0){
		switch ($code) {
			case -1:  $error = '用户名长度必须在6-16个字符以内！'; break;
			case -2:  $error = '用户名被禁止注册！'; break;
			case -3:  $error = '用户名被占用！'; break;
			case -4:  $error = '密码长度必须在6-16个字符之间！'; break;
			case -5:  $error = '邮箱格式不正确！'; break;
			case -6:  $error = '邮箱长度必须在1-32个字符之间！'; break;
			case -7:  $error = '邮箱被禁止注册！'; break;
			case -8:  $error = '邮箱被占用！'; break;
			case -9:  $error = '手机格式不正确！'; break;
			case -10: $error = '手机被禁止注册！'; break;
			case -11: $error = '手机号被占用！'; break;
			default:  $error = '未知错误';
		}
		return $error;
	}
}
?>