<?php

namespace Admin\Controller;

use User\Api\UserApi;
use Admin\Model\AuthRuleModel;
use Admin\Model\AuthGroupModel;

/**
 * 后台用户控制器
 */
class CurrencyController extends AdminController {

    protected $parentid = 0;
    protected $groupid = 0;

    /**
     * 用户列表
     */
    public function index($group_id = 0, $parentid = 0) {

        //时间区间检索
        $create_time_s = I('create_time_s', '');
        $create_time_e = I('create_time_e', '');
        if (!empty($create_time_s) && !empty($create_time_e)) {
            $create_stime = $create_time_s . ' 00:00:00';
            $create_etime = $create_time_e . ' 23:59:59';
            $create_time_s = strtotime($create_stime);
            $create_time_e = strtotime($create_etime);
            $mapp['duoduo_member_recharge_record.create_time'] = array(array('egt', $create_time_s), array('elt', $create_time_e));
            $tmapp['duoduo_member_recharge_record.create_time'] = array(array('egt', $create_time_s), array('elt', $create_time_e));
            $mappp['duoduo_member_recharge_record.create_time']      = array(array('egt', $create_time_s), array('elt', $create_time_e));
            $tmappp['duoduo_member_recharge_record.create_time']      = array(array('egt', $create_time_s), array('elt', $create_time_e));
            $mapppp['duoduo_user_recharge_record.create_time']      = array(array('egt', $create_time_s), array('elt', $create_time_e));
            $mappppp['duoduo_user_used_record.used_time']      = array(array('egt', $create_time_s), array('elt', $create_time_e));
        }    	
        $limit = 20;
        //获取数据
        $MainTab = AuthGroupModel::MEMBER;
        $MainAlias = 'main';
        $MainField = array('uid', 'nickname', 'total_currency', 'current_currency', 'pid');
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
            AuthGroupModel::UCENTER_MEMBER => array('Ralias' => 'uc', 'Ron' => 'uc ON uc.id=main.uid', 'Rfield' => array('username', 'mobile', 'status')),
        );

        $RelationTab = $this->getRelationTab($RelationTab);
        $tables = $RelationTab['tables'];
        $RelationFields = $RelationTab['fields'];
        $model = !empty($tables) ? $MainModel->join($tables, 'LEFT') : $MainModel;
        //检索条件
        $keyword = trim(I('get.find_keyword'));
        //商家所在的用户组
        $usergroup = $this->usergroup;
        $usergroup = array_values($usergroup['gid']);
        if($usergroup[0] == 1 || UID == 1){
        	$uids = D($MainTab)->getChildrenId(0);
        }else{
        	$uids = D($MainTab)->getChildrenId(UID);
        }
        $map['main.uid'] = array('in', $uids);
        //商家是4
        $map['agr.group_id'] = 4;
        
        /* 查询条件初始化 */
        $map['uc.status'] = array('egt', 0);
        if (!empty($keyword)) {
            $map['main.nickname|uc.username'] = array(array('like', '%' . $keyword . '%'), array('like', '%' . $keyword . '%'), '_multi' => true);
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
        $_list = array();
        if (!empty($list)) {
            $status_text = array('禁用', '正常');
            $i = 1;
            //聚蚁币购买金额
            $mapp               =array();
            $mapp[]             ='type=0 and (recharge_type=2 or (recharge_type=1 and pay_status=1))';
            $mapp['mid']         =array('in',$uids);
            $data['total_currency']=M('member_recharge_record')->where($mapp)->sum('currency');
            // echo M('member_recharge_record')->getLastSql();die;
            if(empty($data['total_currency'])){
                $data['total_currency']=0;
            }
            //聚蚁币消费金额
            $mappp['type']        = 1;
            $mappp['mid']         =array('in',$uids);
            $data['xiaofei_currency']=M('member_recharge_record')->where($mappp)->sum('currency');
            //聚蚁币余额
            $data['current_currency']=M('member')->join('left join duoduo_auth_group_access on duoduo_auth_group_access.uid=duoduo_member.uid')->where(array('duoduo_auth_group_access.group_id'=>4, 'duoduo_member.uid'=>array('in',$uids)))->sum('current_currency');
            if(empty($data['current_currency'])){
                $data['current_currency ']=0;
            }
            //聚蚁币赠送数量
            $mapppp['mid']         =array('in',$uids);
            $data['send_currency']=M('user_recharge_record')->where($mapppp)->sum('currency');            
            //聚蚁币使用数量
            $mapppp['mid']         =array('in',$uids);
            $data['used_currency']=M('user_used_record')->where($mappppp)->sum('used_currency'); 
            //会员聚蚁币未使用数量
            $data['using_currency']= $data['send_currency']-$data['used_currency']; 
            if($data['using_currency']<0){
                $data['using_currency']=0;
            } 
       
            foreach ($list as $k => $v) {
            	//序号
            	$_list[$k]['i'] 							= $i++;
            	//商家uid
            	$_list[$k]['uid'] 							= $v['uid'];
            	//商家账号
            	$_list[$k]['username'] 						= $v['username'];
            	//商家名称
            	$_list[$k]['nickname'] 						= empty($v['nickname'])?'--':$v['nickname'];
            	//二级代理名称
            	$secondUsername = M('ucenterMember')->where(array('id'=>$v['pid']))->getField('username');
            	$_list[$k]['secondUsername']				= $secondUsername;
            	//一级代理名称
            	$secondPid = M('member')->where(array('uid'=>$v['pid']))->getField('pid');
            	$firstUsername = M('ucenterMember')->where(array('id'=>$secondPid))->getField('username');
            	$_list[$k]['firstUsername']					= $firstUsername;
                //聚蚁币购买金额
                $tmapp[]             ='type=0 and (recharge_type=2 or (recharge_type=1 and pay_status=1))';
                $tmapp['mid']         =$v['uid'];
                $_list[$k]['total_currency']=M('member_recharge_record')->where($tmapp)->sum('currency');
                if(empty($_list[$k]['total_currency'])){
                    $_list[$k]['total_currency']=0;
                }
                //聚蚁币消费总额
                $tmappp['type']      = 1;
                $tmappp['mid']       =$v['uid'];
                $_list[$k]['xiaofei_currency']=M('member_recharge_record')->where($tmappp)->sum('currency');
            	//聚蚁币余额
            	$_list[$k]['current_currency']				=M('member')->where(array('uid'=>$v['uid']))->sum('current_currency');
                if(empty($_list[$k]['current_currency'])){
                    $_list[$k]['current_currency']=0;
                }
            	//聚蚁币赠送数量  
                $mapppp['mid']=$v['uid']; 
                $_list[$k]['send_currency']=M('user_recharge_record')->where($mapppp)->sum('currency'); 
               if(empty($_list[$k]['send_currency'])){
                    $_list[$k]['send_currency']=0;
                }    
                //聚蚁币使用数量
                $mappppp['mid']=$v['uid'];
                $_list[$k]['used_currency']=M('user_used_record')->where($mappppp)->sum('used_currency'); 
               if(empty($_list[$k]['used_currency'])){
                    $_list[$k]['used_currency']=0;
                } 
                //会员聚蚁币未使用数量
                $_list[$k]['using_currency']= $_list[$k]['send_currency']-$_list[$k]['used_currency']; 
               if(empty($_list[$k]['using_currency'])){
                    $_list[$k]['using_currency']=0;
                } 
            	
            }
        }
        $this->assign('_list', $_list);
        $this->assign('data', $data);
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

    //管理员
    public function user() {
    	
    	
    	
        $limit = 20;
        //获取数据
        //$MainTab = 'userUsedRecord';
        
        $MainTab = 'userRechargeRecord';
        $MainAlias = 'main';
        $MainField = array('id', 'mid', 'uid', 'sum(main.currency) as total_currency', 'sum(main.current_currency) as total_current_currency', 'max(main.create_time) as max_create_time','integral', 'markers', 'is_expired');
        //主表模型
        $MainModel = M($MainTab)->alias($MainAlias);
        /*
         * 灵活定义关联查询
         * Ralias 	关联表别名
         * Ron    	关联条件
         * Rfield	关联查询字段，
         * */
        $RelationTab = array(
            'user' => array('Ralias' => 'u', 'Ron' => 'u ON main.uid = u.id', 'Rfield' => array('phone', 'nickname')),
        );
        $RelationTab = $this->getRelationTab($RelationTab);
        $tables = $RelationTab['tables'];
        $RelationFields = $RelationTab['fields'];
        $model = !empty($tables) ? $MainModel->join($tables, 'LEFT') : $MainModel;
        //检索条件
        $keyword = trim(I('get.find_keyword'));
        
        /* 查询条件初始化 */
        if (!empty($keyword)) {
            $map['u.phone|u.nickname'] = array(array('like', '%' . $keyword . '%'), array('like', '%' . $keyword . '%'), '_multi' => true);
        }
        $id = I('get.id', '0', 'intval');
        if ($id > 0) {
            $map['main.mid'] = $id;
        }
        //排序
        $order = $MainAlias . '.uid asc';
        //时间区间检索
        $search_time				= time_between('create_time',$MainAlias);
        $map						= array_merge($map,$search_time);
        //检索字段
        $fields = (empty($MainField) ? $this->get_fields_string($MainModel->getDbFields(), $MainAlias) . ',' : $this->get_fields_string($MainField, $MainAlias) . ',') . $RelationFields;
        $fields = trim($fields, ',');
        $group = 'main.uid';
        
        //列表数据
        $list = $this->getLists($model, $map, $order, $fields, $page, $limit, true, $group);
        //总数量
        //$total_currency = M('userRechargeRecord')->alias('main')->where($map)->sum('currency');
        $total_currency = M('userRechargeRecord')->alias('main')->join('__USER__ u ON main.uid = u.id')->where($map)->sum('currency');
        
        //未使用数量
        //$total_current_currency = M('userRechargeRecord')->alias('main')->where($map)->sum('current_currency');
        $total_current_currency = M('userRechargeRecord')->alias('main')->join('__USER__ u ON main.uid = u.id')->where($map)->sum('current_currency');
        //使用数量
        $total_used_currency = intval($total_currency)-intval($total_current_currency);
        $this->assign('total_currency', intval($total_currency));
        $this->assign('total_current_currency', intval($total_current_currency));
        $this->assign('total_used_currency', $total_used_currency);
        
        $_list = array();
        if (!empty($list)) {
            $status_text = array('禁用', '正常');
            $i = 1;
            foreach ($list as $k => $v) {
            	//序号
            	$_list[$k]['i'] 							= $i++;
            	//会员账号
            	$_list[$k]['phone'] 						= $v['phone'];
            	//会员昵称
            	$_list[$k]['nickname'] 						= empty($v['nickname'])?0:$v['nickname'];
            	//总数量
            	$_list[$k]['total_currency']				= empty($v['total_currency'])?0:$v['total_currency'];
            	//未使用数量
            	$_list[$k]['total_current_currency']		= empty($v['total_current_currency'])?0:$v['total_current_currency'];
            	//使用数量
            	$_list[$k]['total_used_currency']			= $_list[$k]['total_currency']-$_list[$k]['total_current_currency'];
            	//时间
            	$_list[$k]['max_create_time']				= date('Y-m-d H:i:s', $v['max_create_time']);
            }
        }
        $this->assign('_list', $_list);

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
        $this->display('user');
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
        );
        return $FormData[$index];
    }
}

?>