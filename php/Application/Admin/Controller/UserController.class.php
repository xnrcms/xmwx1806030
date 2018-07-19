<?php

namespace Admin\Controller;

use User\Api\UserApi;
use Admin\Model\AuthRuleModel;
use Admin\Model\AuthGroupModel;

/**
 * 后台用户控制器
 */
class UserController extends AdminController {

    protected $parentid = 0;
    protected $groupid = 0;

    /**
     * 用户列表
     */
    public function index($group_id = 0, $parentid = 0) {
        $limit = 20;
        //获取数据
        $MainTab = AuthGroupModel::MEMBER;
        $MainAlias = 'main';
        $MainField = array('uid', 'nickname', 'last_login_time', 'last_login_ip', 'account', 'login');
        //主表模型
        $MainModel = M($MainTab)->alias($MainAlias);
        /*
         * 灵活定义关联查询
         * Ralias 	关联表别名
         * Ron    	关联条件
         * Rfield	关联查询字段，
         * */
        $RelationTab = array(
            AuthGroupModel::AUTH_GROUP_ACCESS => array('Ralias' => 'agr', 'Ron' => 'agr ON main.uid=agr.uid', 'Rfield' => false),
            AuthGroupModel::UCENTER_MEMBER => array('Ralias' => 'uc', 'Ron' => 'uc ON uc.id=main.uid', 'Rfield' => array('username', 'mobile', 'email', 'status')),
        );

        $RelationTab = $this->getRelationTab($RelationTab);
        $tables = $RelationTab['tables'];
        $RelationFields = $RelationTab['fields'];
        $model = !empty($tables) ? $MainModel->join($tables, 'LEFT') : $MainModel;
        //检索条件
        $keyword = trim(I('get.find_keyword'));
        if (UID != 1) {
            $map['id'] = UID;
        }
        /* 查询条件初始化 */
        $map['uc.status'] = array('egt', 0);
        if (!empty($keyword)) {
            $map['main.uid|main.nickname|uc.username|uc.email|uc.mobile'] = array(intval($keyword), array('like', '%' . $keyword . '%'), array('like', '%' . $keyword . '%'), array('like', '%' . $keyword . '%'), '_multi' => true);
        }
        if ($group_id > 0) {
            $map['agr.group_id'] = $group_id;
        }
        if ($parentid > 0) {
            $map['main.parentid'] = $parentid;
        }
        //排序
        $order = $MainAlias . '.uid desc';
        //检索字段
        $fields = (empty($MainField) ? $this->get_fields_string($MainModel->getDbFields(), $MainAlias) . ',' : $this->get_fields_string($MainField, $MainAlias) . ',') . $RelationFields;
        $fields = trim($fields, ',');
        //列表数据
        $list = $this->getLists($model, $map, $order, $fields, $page, $limit, true);
        if (!empty($list)) {
            $status_text = array('禁用', '正常');
            foreach ($list as $k => $v) {
                //数据格式化
                $list[$k]['status_text'] = $status_text[$v['status']];
            }
        }
        $this->assign('_list', $list);

        //操作菜单
        $SonMenu = $this->getSonMenu(5);
        $this->assign('ListTopNav', !empty($SonMenu['TOPMENU']) ? $SonMenu['TOPMENU'] : array());
        $this->assign('ListRightNav', !empty($SonMenu['RIGHTMENU']) ? $SonMenu['RIGHTMENU'] : array());

        $this->NavTitle = '用户管理';
        $this->extends_param = '&menuid=' . $this->menuid;
        //记录当前列表页的cookie
        if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf'))
            Cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->display('index');
    }

    //管理员
    public function adminuser() {
        $this->groupid = 1;
        session('administor_groupid_info', $this->groupid);
        $this->assign('SmallNav', array('用户管理', '管理员列表'));
        $this->index($this->groupid);
    }

    //一级代理
    public function firstAgentsUser() {
        $this->groupid = 2;
        session('administor_groupid_info', $this->groupid);
        $this->assign('SmallNav', array('用户管理', '用户列表'));
        $this->index($this->groupid);
    }

    //二级代理
    public function twoAgentsUser() {
        $this->groupid = 3;
        session('administor_groupid_info', $this->groupid);
        $this->assign('SmallNav', array('用户管理', '用户列表'));
        $this->index($this->groupid);
    }

    //商家用户
    public function businessUser() {
        $this->groupid = 4;
        session('administor_groupid_info', $this->groupid);
        $this->assign('SmallNav', array('用户管理', '用户列表'));
        $this->index($this->groupid);
    }

    //用户
    public function user() {
        $this->groupid = 5;
        session('administor_groupid_info', $this->groupid);
        $this->assign('SmallNav', array('用户管理', '用户列表'));
        $this->index($this->groupid);
    }

    //可继续扩展更多类型用户....................
    //.....................................

    public function delUser() {
        $this->changeStatus('deleteuser');
    }

    public function forbidUser() {
        $this->changeStatus('forbiduser');
    }

    public function resumeUser() {
        $this->changeStatus('resumeuser');
    }

    /**
     * 添加用户账号
     */
    public function addUser() {
        if (IS_POST)
            $this->submitAdd();
        $FormData = $this->CustomerForm(0);
        $this->assign('FormData', $FormData);
        //用户组
        $gid = session('administor_groupid_info');
        $groupInfo = M(AuthGroupModel::AUTH_GROUP)->field('id,title')->getById($gid);
        if (!$groupInfo['id'])
            $this->error('用户组不存在！');
        $parentTitle = $this->parentid > 0 ? '管理员' : '';
        $userTitle = empty($groupInfo['title']) ? '用户' : $groupInfo['title'] . $parentTitle;
        $this->NavTitle = '新增' . $userTitle;
        $this->display('add');
    }

    /**
     * 编辑用户账号
     */
    public function editUser() {
        if (IS_POST) $this->submitEdit();
        $uid = I('request.uid', 0, 'intval');
        $hashid = I('request.hashid', '');
        if (!$this->checkId($uid, $hashid)) $this->error('非法参数！');
        
        $FormData = $this->CustomerForm(1);
        $this->assign('FormData', $FormData);

        $memberInfo = M('ucenter_member')->where(array('id' => $uid))->field(array('id', 'username', 'mobile', 'email', 'status'))->find();
        $memberInfo['uid'] = $uid;
        $memberInfo['hashid'] = $hashid;
        
        $pid = M('member')->where(array('uid'=>$uid))->getField('pid');
        if(!empty($pid)){
        	$memberInfo['pid'] = $pid;
        }
        $this->assign('info', $memberInfo);

        $groupInfo = M(AuthGroupModel::AUTH_GROUP_ACCESS)->field('group_id')->getByUid($uid);
        if (empty($groupInfo) || $groupInfo['group_id'] <= 0) {
            $this->error("权限组错误!", Cookie('__forward__'));
        }
        //用户组
        $gid = $groupInfo['group_id'];
        $groupInfo = M(AuthGroupModel::AUTH_GROUP)->field('id,title')->getById($gid);
        $parentTitle = $this->parentid > 0 ? '管理员' : '';
        $userTitle = empty($groupInfo['title']) ? '用户' : $groupInfo['title'] . $parentTitle;
        $this->NavTitle = '编辑' . $userTitle;
        $this->display('edit');
    }

    /**
     * 修改密码表单
     */
    public function updatepassword() {
        if (IS_POST)
            $this->submitPassword();
        $FormData = $this->CustomerForm(2);
        $this->assign('FormData', $FormData);

        $this->NavTitle = '密码修改';
        $this->display();
    }

    /**
     * 资料修改
     */
    public function updateMemeber() {
        if (IS_POST)
            $this->submitMemeber();
        $uid = I('request.uid', 0, 'intval');
        $hashid = I('request.hashid', '');
        if (!$this->checkId($uid, $hashid))
            $this->error('非法参数！');

        $FormData = $this->CustomerForm(3);
        $this->assign('FormData', $FormData);

        $info = M('Member')->where(array('uid' => $uid))->field(true)->find();
        $info['hashid'] = $hashid;
        $this->assign('info', $info);

        $this->NavTitle = '资料修改';
        $this->display();
    }

    /**
     * 修改资料
     */
    protected function submitMemeber() {
        //获取参数
        $uid = I('post.uid');
        $hashid = I('post.hashid');
        $face = intval(I('post.face'));
        $nickname = I('post.nickname');
        if (!$this->checkId($uid, $hashid))
            $this->error('非法参数！');
        if(empty($nickname)){
                $this->error('请输用户昵称');           
        }

        $updata['nickname'] = $nickname;
        $updata['face'] = $face;
        $updata['shopface'] = $shopface;
        $updata['uid'] = $uid;

        $Member = D('Member');
        $res = $Member->update($updata);
        if ($res) {
            $this->success('资料修改成功！', Cookie('__forward__'));
        } else {
            $this->error($Member->getError());
        }
    }

    /**
     * 用户状态修改
     */
    protected function changeStatus($method = null) {
        $id = array_unique((array) I('request.ids'));
        if (in_array(C('USER_ADMINISTRATOR'), $id)) {
            $this->error("不允许对超级管理员执行该操作!");
        }
        $uid[] = 0;
        foreach ($id as $vid) {
            $checkid = explode('-', $vid);
            if (!$this->checkId($checkid[0], $checkid[1]))
                $this->error('非法参数！');
            $uid[] = $checkid[0];
        }
        if (empty($uid)) {
            $this->error('请选择要操作的数据!');
        }
        $map['id'] = array('in', $uid);
        switch (strtolower($method)) {
            case 'forbiduser': $this->forbid('UcenterMember', $map, array('url' => Cookie('__forward__'), 'success' => '禁用成功！', 'error' => '禁用失败！'));
                break;
            case 'resumeuser': $this->resume('UcenterMember', $map, array('url' => Cookie('__forward__'), 'success' => '启用成功！', 'error' => '启用失败！'));
                break;
            case 'deleteuser':
                M('Member')->where(array('uid' => array('in', $id)))->delete();
                M('UcenterMember')->where($map)->delete();
                $this->success('删除成功', Cookie('__forward__'));
                break;
            default:
                $this->error('参数非法');
        }
    }

    /**
     * 修改密码提交
     */
    protected function submitPassword() {
        //获取参数
        $old = I('post.old');
        $password = I('post.password');
        $repassword = I('post.repassword');
        empty($old) && $this->error('请输入原密码');
        empty($password) && $this->error('请输入新密码');
        empty($repassword) && $this->error('请输入确认密码');
        if ($password !== $repassword)
            $this->error('您输入的新密码与确认密码不一致');
        //密码修改
        $Api = new UserApi();
        $res = $Api->updateInfo(UID, $old, array('password' => $password));
        if ($res['status']) {
            $this->success('修改密码成功！');
        } else {
            $this->error($res['info']);
        }
    }

    /**
     * 添加用户提交
     */
    protected function submitAdd() {
        if (IS_POST) {
            //用户组
            $gid = session('administor_groupid_info');
            $groupInfo = M(AuthGroupModel::AUTH_GROUP)->field('id,title')->getById($gid);
            if (!$groupInfo['id']) $this->error('用户组不存在！');
            //提交数据
            $pid = I('post.pid', '', 'trim');
            if($gid == 1){			//平台父级为空
            	$pid = '';
            }elseif($gid == 2){		//一级代理父级为0
            	$pid = 0;	
            }elseif($gid == 3){		//二级代理父级为一级代理的id
            	$pid = intval($pid);
            	if($pid <= 0){
            		$this->error('请选择一级代理！');
            	}
            }elseif($gid == 4){		//商家父级为二级代理的id
            	$pid = intval($pid);
            	if($pid <= 0){
            		$this->error('请选择二级代理！');
            	}
            }
            $username = I('post.username', '');
            $password = I('post.password', '');
            $repassword = I('post.repassword', '');
            $email = I('post.email', '');
            $mobile = I('post.mobile', '');
            /* 检测密码 */
            if ($password != $repassword) $this->error('密码和重复密码不一致！');
            /* 调用注册接口注册用户 */
            $User = new UserApi;
            $uid = $User->register($username, $password, $email, $mobile);
            if (0 < $uid) { //注册成功
                $user = array('uid' => $uid, 'nickname' => $username, 'status' => 1, 'pid' => $pid);
                if($user['pid'] === ''){
                	//保证平台父级为空
                	unset($user['pid']);
                }
                if (!M('Member')->add($user)) {
                    $this->error('用户添加失败！');
                } else {
                    if ($uid > 0 && $gid > 0) {
                        //根据分组添加用户
                        $AuthGroup = D('AuthGroup');
                        if ($gid && !$AuthGroup->checkGroupId($gid)) {
                            $this->error($AuthGroup->error);
                        }
                        if ($AuthGroup->addToGroup($uid, $gid)) {
                            $this->success('用户添加成功！', Cookie('__forward__'));
                        }
                    }
                    $this->success('用户添加成功！', Cookie('__forward__'));
                }
            } else { //注册失败，显示错误信息
                $this->error($this->showRegError($uid));
            }
        } else {
            $this->error('非法提交');
        }
    }

    /**
     * 用户编辑提交
     */
    protected function submitEdit() {
        if (IS_POST) {
            $uid = I('post.id', 0, 'intval');
            $hashid = I('post.hashid', '');
            if (!$this->checkId($uid, $hashid))
                $this->error('非法参数！');
            $memberDb = M('ucenter_member');
            //更改密码
            $password = I('post.password', '');
            if ($password != '') {
                import('User.Conf.config', APP_PATH, '.php');
                $pass_key = md5(sha1($password) . UC_AUTH_KEY);
                $updata['password'] = $pass_key;
            }
            $mobile = I('post.mobile');
            $email = I('post.email');
            $status = I('post.status');
            
            if (!empty($mobile)) {
                if (!Mobile_check($mobile)) {
                    $this->error('请输入正确格式的手机号码');
                }
                $updata['mobile'] = $mobile;
            }
            if (!empty($email)) {
                if (!Email_check($email)) {
                    $this->error('请输入正确格式的邮箱账号');
                }
                $updata['email'] = $email;
            }
            $updata['status'] = intval(I('post.status'));
            
            if(isset($_POST['pid'])){
            	$pid = I('post.pid', '', 'intval');
            	if($pid <= 0){
            		$this->error('请选择父级！');
            	}
            	M('member')->where(array('uid' => $uid))->save(array('pid'=>$pid));
            }
            $res = M('ucenter_member')->where(array('id' => $uid))->save($updata);
            if (false !== $res) {
                $this->success('修改成功！', Cookie('__forward__'));
            } else {
                $this->error($memberDb->getError());
            }
        }
    }
    
    protected function getPidData($pGid){
    	$pidArr = M('UcenterMember')->alias('um')
    	->join(array(' LEFT JOIN __AUTH_GROUP_ACCESS__ aga ON um.id = aga.uid'))
    	->field('um.id, um.username')
    	->where(array('aga.group_id'=>$pGid))
    	->select();
    	return $pidArr;
    	/* $pidData					= array(''=>'-请选择-');
    	if(!empty($pidArr)){
    		foreach ($pidArr as $k => $v){
    			$pidData[$v['id']] 	= $v['username'];
    		}
    	} */
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

    protected function CustomerForm($index = 0) {
        //账号添加
        $FormData[0] = array(
            array('fieldName' => '用户名', 'fieldValue' => 'username', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入用户名" autocomplete="off"'),
            array('fieldName' => '登录密码', 'fieldValue' => 'password', 'fieldType' => 'password', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入登录密码" autocomplete="off"'),
            array('fieldName' => '确认密码', 'fieldValue' => 'repassword', 'fieldType' => 'password', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入确认密码" autocomplete="off"'),
            array('fieldName' => '邮箱账号', 'fieldValue' => 'email', 'fieldType' => 'text', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入邮箱账号" autocomplete="off"'),
            array('fieldName' => '手机号码', 'fieldValue' => 'mobile', 'fieldType' => 'text', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入手机号码" autocomplete="off"'),
            //array('fieldName' => '商户名称', 'fieldValue' => 'shopname', 'fieldType' => 'text', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入商户名称" autocomplete="off"'),
            //array('fieldName' => '地图定位', 'fieldValue' => 'position', 'fieldType' => 'position', 'isMust' => 1, 'fieldData' => array('type' => 3), 'attrExtend' => ''),
        );
        
        //账号编辑
        $FormData[1] = array(
            array('fieldName' => '用户名', 'fieldValue' => 'username', 'fieldType' => 'text', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入用户名" disabled'),
            array('fieldName' => '登录密码', 'fieldValue' => 'password', 'fieldType' => 'password', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'autocomplete="off" placeholder="留空则不修改"'),
            array('fieldName' => '邮箱账号', 'fieldValue' => 'email', 'fieldType' => 'text', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入邮箱账号"'),
            array('fieldName' => '手机号码', 'fieldValue' => 'mobile', 'fieldType' => 'text', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入手机号码"'),
            array('fieldName' => '用户状态', 'fieldValue' => 'status', 'fieldType' => 'radio', 'isMust' => 0, 'fieldData' => array('1' => '启用', '0' => '禁用'), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '隐藏域', 'fieldValue' => array('id', 'hashid'), 'fieldType' => 'hidden', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            //array('fieldName' => '商户名称', 'fieldValue' => 'shopname', 'fieldType' => 'text', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入商户名称" autocomplete="off"'),
            //array('fieldName' => '地图定位', 'fieldValue' => 'position', 'fieldType' => 'position', 'isMust' => 1, 'fieldData' => array('type' => 3), 'attrExtend' => ''),
        );
        //pid	平台 一级 二级 商户
        $gid = session('administor_groupid_info');
        if(in_array($gid, array('3','4'))){
        	if($gid == 3){
        		$fieldName 				= '一级代理';
        		$pGid 					= 2;
        	}elseif($gid == 4){
        		$fieldName 				= '二级代理';
        		$pGid 					= 3;
        	}
        	$pidData					= array(''=>'-请选择-');
        	$pidArr = $this->getPidData($pGid);
        	if(!empty($pidArr)){
        		foreach ($pidArr as $k => $v){
        			$pidData[$v['id']] 	= $v['username'];
        		}
        	}
        	array_unshift($FormData[0], array('fieldName'=>$fieldName,'fieldValue'=>'pid','fieldType'=>'select','isMust'=>1,'fieldData'=>$pidData,'attrExtend'=>''));
        	array_unshift($FormData[1], array('fieldName'=>$fieldName,'fieldValue'=>'pid','fieldType'=>'select','isMust'=>1,'fieldData'=>$pidData,'attrExtend'=>''));
        }
        //密码修改
        $FormData[2] = array(
            array('fieldName' => '原始密码', 'fieldValue' => 'old', 'fieldType' => 'password', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'autocomplete="off" placeholder="请输入原始密码"'),
            array('fieldName' => '新密密码', 'fieldValue' => 'password', 'fieldType' => 'password', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入登录密码" autocomplete="off"'),
            array('fieldName' => '确认密码', 'fieldValue' => 'repassword', 'fieldType' => 'password', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入确认密码" autocomplete="off"'),
        );
        //资料修改
        $FormData[3] = array(
            array('fieldName' => '用户头像', 'fieldValue' => 'face', 'fieldType' => 'image', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'data-table="member" data-field="face" data-size=""'),
            array('fieldName' => '用户昵称', 'fieldValue' => 'nickname', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入用户昵称"'),
            array('fieldName' => '隐藏域', 'fieldValue' => array('uid', 'hashid'), 'fieldType' => 'hidden', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
        );
        return $FormData[$index];
    }

    /**
     * 获取用户注册错误信息
     * @param  integer $code 错误编码
     * @return string        错误信息
     */
    private function showRegError($code = 0) {
        switch ($code) {
            case -1: $error = '用户名长度必须在6-16个字符以内！';
                break;
            case -2: $error = '用户名被禁止注册！';
                break;
            case -3: $error = '用户名被占用！';
                break;
            case -4: $error = '密码长度必须在6-30个字符之间！';
                break;
            case -5: $error = '邮箱格式不正确！';
                break;
            case -6: $error = '邮箱长度必须在1-32个字符之间！';
                break;
            case -7: $error = '邮箱被禁止注册！';
                break;
            case -8: $error = '邮箱被占用！';
                break;
            case -9: $error = '手机格式不正确！';
                break;
            case -10: $error = '手机被禁止注册！';
                break;
            case -11: $error = '手机号被占用！';
                break;
            default: $error = '未知错误';
        }
        return $error;
    }

}

?>