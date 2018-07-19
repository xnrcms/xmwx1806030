<?php

namespace Admin\Controller;

use User\Api\UserApi;
use Admin\Model\AuthRuleModel;
use Admin\Model\AuthGroupModel;

/**
 * 后台用户控制器
 */
class CurrencyRechargeController extends AdminController {

    protected $parentid = 0;
    protected $groupid = 0;

    /**
     * 用户列表
     */
    public function index($group_id = 0, $parentid = 0) {
    	$group_id = 4;
        $limit = 20;
        //获取数据
        $MainTab = AuthGroupModel::MEMBER;
        $MainAlias = 'main';
        $MainField = array('uid', 'nickname', 'last_login_time', 'last_login_ip', 'account', 'login', 'total_currency', 'current_currency', 'total_integral', 'current_integral');
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
        $menuid								= $this->menuid;
        $SonMenu							= $this->getSonMenu($menuid);
        $this->assign('ListTopNav', !empty($SonMenu['TOPMENU']) ? $SonMenu['TOPMENU'] : array());
        $this->assign('ListRightNav', !empty($SonMenu['RIGHTMENU']) ? $SonMenu['RIGHTMENU'] : array());

        $this->NavTitle = '用户管理';
        $this->extends_param = '&menuid=' . $this->menuid;
        //记录当前列表页的cookie
        if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf'))
            Cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->display('index');
    }
 

    /**
     * 积分充值
     */
    public function recharge() {
        if (IS_POST)
            $this->submitMemeber();
        $uid = I('request.uid', 0, 'intval');
        $hashid = I('request.hashid', '');
        if (!$this->checkId($uid, $hashid))
            $this->error('非法参数！');

        $FormData = $this->CustomerForm(0);
        $this->assign('FormData', $FormData);

        $info             = M('Member')->where(array('uid' => $uid))->field(true)->find();
        $info['username'] = M('ucenter_member')->where(array('id' => $uid))->getField('username');
        $info['hashid'] = $hashid;
        $this->assign('info', $info);

        $this->NavTitle = '积分充值';
        $this->display();
    }

    /**
     * 修改资料
     */
    protected function submitMemeber() {
        //获取参数
        $uid         = I('post.uid');
        $hashid      = I('post.hashid');
        $currency    = I('post.currency', '', 'trim');
        $recurrency  = I('post.recurrency', '', 'trim');
        if($currency == ''){
        	$this->error('请输入积分');
        }
        if ($currency != $recurrency) {
                $this->error('两次积分输入不一致！！！');
        }
        if(!preg_match("/^[1-9]\d*$/",$currency)){
        	$this->error('充值金额必须为大于0的整数！');
        }
        $markers = I('post.markers', '', 'trim');
        if (!$this->checkId($uid, $hashid)) $this->error('非法参数！');
        $data = array();
        $data['total_currency'] 		= array('exp',"total_currency+$currency");
        $data['current_currency'] 		= array('exp',"current_currency+$currency");
        $data['total_integral'] 		= array('exp',"total_integral+$currency");
        $data['current_integral'] 		= array('exp',"current_integral+$currency");
        $res = M('member')->where(array('uid'=>$uid))->save($data); // 根据条件保存修改的数据
        if ($res !== false) {
        	$data = array();
        	//商户订单号
        	$data['out_trade_no'] 	= '';
        	$data['mid'] 			= $uid;
        	$data['money'] 			= 0;
        	$data['currency'] 		= $data['integral'] = $currency;
        	$data['markers']		= $markers;
        	$data['create_time'] 	= NOW_TIME;
        	$data['recharge_type'] 	= 2;	//1商户支付宝购买 2平台给商户充值
        	$data['type'] 			= 0;	//0收入 1支出
        	/* 添加或更新数据 */
        	$result = M('memberRechargeRecord')->add($data);
        	if($result){
        		$this->success('充值成功！', Cookie('__forward__'));
        	}
        }
        $this->error(M('member')->getError());
    }
    
    /**
     * 银行账号
     */
    public function card() {
    	if (IS_POST){
    		$bank_name = I('post.bank_name', '', 'trim');
    		$card_number = I('post.card_number', '', 'trim');
    		$user_name = I('post.user_name', '', 'trim');
    		if($bank_name == ''){
    			$this->error('请输入开户银行');
    		}
    		if($card_number == ''){
    			$this->error('请输入开户账号');
    		}
    		if($user_name == ''){
    			$this->error('请输入开户名称');
    		}
    		$data = array();
    		$data['bank_name'] = $bank_name;
    		$data['card_number'] = $card_number;
    		$data['user_name'] = $user_name;
    		$bankCard = M('bankCard')->find();
    		if($bankCard['id']){
    			$res = M('bankCard')->where(array('id'=>$bankCard['id']))->save($data);
    			if($res !== false){
    				$this->success('修改成功！', Cookie('__forward__'));
    			}
    		}else{
    			$res = M('bankCard')->add($data);
	    		if($res){
	        		$this->success('添加成功！', Cookie('__forward__'));
	        	}
    		}
    		$this->error(M('bankCard')->getError());
    	}else{
    		$FormData = $this->CustomerForm(1);
    		$this->assign('FormData', $FormData);
    		$info = M('bankCard')->field(true)->find();
    		$this->assign('info', $info);
    		$this->NavTitle = '银行账号设置';
    		Cookie('__forward__',$_SERVER['REQUEST_URI']);
    		$this->display();
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

    protected function CustomerForm($index = 0) {
 
        //积分充值
        $FormData[0] = array(
            array('fieldName' => '用户名', 'fieldValue' => 'username', 'fieldType' => 'show', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入用户名"'),
            array('fieldName' => '昵称', 'fieldValue' => 'nickname', 'fieldType' => 'show', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入昵称"'),
            array('fieldName' => '充值积分', 'fieldValue' => 'currency', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入积分"'),
           array('fieldName' => '确认积分', 'fieldValue' => 'recurrency', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入确认积分"'),
        	array('fieldName'=>'备注','fieldValue'=>'markers','fieldType'=>'textarea','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入备注" rows="5" style="height:100%;"'),
            array('fieldName' => '隐藏域', 'fieldValue' => array('uid', 'hashid'), 'fieldType' => 'hidden', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
        );
        //银行账号
        $FormData[1] = array(
        		array('fieldName' => '开户银行', 'fieldValue' => 'bank_name', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入开户银行"'),
        		array('fieldName' => '开户账号', 'fieldValue' => 'card_number', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入开户账号"'),
        		array('fieldName' => '开户名称', 'fieldValue' => 'user_name', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入开户名称"'),
        );
        return $FormData[$index];
    }



}

?>