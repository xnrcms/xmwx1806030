<?php

namespace Admin\Controller;

use User\Api\UserApi;
use Admin\Model\AuthRuleModel;
use Admin\Model\AuthGroupModel;

/**
 * 后台用户控制器
 */
class UsersController extends AdminController {



    /**
     * 用户列表
     */
    public function index() {
        $limit = 20;
        //获取数据
        $MainTab = 'user';
        $MainAlias = 'main';
        $MainField = array('id', 'phone', 'avatar', 'nickname', 'age', 'total_money','current_money','apply_cash_now','already_crash','forbidden_cash','total_integral','current_integral','total_coin','current_coin','status');
        //主表模型
        $MainModel = M($MainTab)->alias($MainAlias);
        /*
         * 灵活定义关联查询
         * Ralias   关联表别名
         * Ron      关联条件
         * Rfield   关联查询字段，
         * */
        $RelationTab                = array(
        //'member'=>array('Ralias'=>'me','Ron'=>'me ON me.uid=main.uid','Rfield'=>array('uid as uuid','nickname')),
        );
        $RelationTab                = $this->getRelationTab($RelationTab);
        $tables                     = $RelationTab['tables'];
        $RelationFields             = $RelationTab['fields'];
        $model                      = !empty($tables) ? $MainModel->join ( $tables ,'LEFT') : $MainModel;

        //检索条件
        $map                        = array();
        //检索条件
        $keyword = trim(I('get.find_keyword'));
        /* 查询条件初始化 */
        if (!empty($keyword)) {
            $map['main.nickname|main.phone'] = array(array('like', '%' . $keyword . '%'), array('like', '%' . $keyword . '%'), '_multi' => true);
        }
        //排序
        $order = $MainAlias . '.id desc';
        //检索字段
        $fields = (empty($MainField) ? $this->get_fields_string($MainModel->getDbFields(), $MainAlias) . ',' : $this->get_fields_string($MainField, $MainAlias) . ',') . $RelationFields;
        $fields = trim($fields, ',');
        //列表数据
        $list = $this->getLists($model, $map, $order, $fields, $page, $limit, true);
        if (!empty($list)) {
            $status = array('禁用', '正常');
            foreach ($list as $k => $v) {
                //数据格式化
                $list[$k]['_status']      = $status[$v['status']];
                $list[$k]['current_coin']=get_currency($v['id']);
            }
        }
        $this->assign('_list', $list);
        //操作菜单,可以根据需要固定$menuid,$menuid为Menu表中的ID
        $menuid   = $this->menuid;
        $SonMenu  = $this->getSonMenu($menuid);
        $this->assign('ListTopNav', !empty($SonMenu['TOPMENU']) ? $SonMenu['TOPMENU'] : array());
        $this->assign('ListRightNav', !empty($SonMenu['RIGHTMENU']) ? $SonMenu['RIGHTMENU'] : array());

        $this->NavTitle = '用户管理';
        $this->extends_param = '&menuid=' . $this->menuid;
        //记录当前列表页的cookie
        if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf'))
            Cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->display('index');
    }

    public function forbidUser() {
        $id=I('ids','0');
        $res=M('user')->data(array('status'=>0))->where(array('id'=>$id[0]))->save();
        if ($res !== false) {
            //给用户推送消息
            $this->success('操作成功', Cookie('__forward__'));
        } else {
            $this->error('操作失败', Cookie('__forward__'));
        }
    }

    public function resumeUser() {
        $id=I('ids','0');
        $res=M('user')->data(array('status'=>1))->where(array('id'=>$id[0]))->save();
        if ($res !== false) {
            //给用户推送消息
            $this->success('操作成功', Cookie('__forward__'));
        } else {
            $this->error('操作失败', Cookie('__forward__'));
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