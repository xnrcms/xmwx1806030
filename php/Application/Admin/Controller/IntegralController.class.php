<?php

namespace Admin\Controller;

use User\Api\UserApi;
use Admin\Model\AuthRuleModel;
use Admin\Model\AuthGroupModel;

/**
 * 后台用户控制器
 */
class IntegralController extends AdminController {

    protected $parentid = 0;
    protected $groupid = 0;

    /**
     * 用户列表
     */
    public function index() {

        $pid     = I('get.pid',0,'intval');
        $type    = I('get.type',0,'intval');
        //时间区间检索
        $create_time_s = I('create_time_s', '');
        $create_time_e = I('create_time_e', '');
        if (!empty($create_time_s) && !empty($create_time_e)) {
            $create_stime = $create_time_s . ' 00:00:00';
            $create_etime = $create_time_e . ' 23:59:59';
            $create_time_s = strtotime($create_stime);
            $create_time_e = strtotime($create_etime);
            $mapp['duoduo_member_recharge_record.create_time'] = array(array('egt', $create_time_s), array('elt', $create_time_e));
            $mappp['duoduo_member_recharge_record.create_time']      = array(array('egt', $create_time_s), array('elt', $create_time_e));
        }

        $group_id=M('auth_group_access')->where(array('uid'=>UID))->getField('group_id');
        $uid     =UID;
        if((UID==1 || $group_id==1) && $type==''){
            $limit = 20;
            //获取数据
            $MainTab = AuthGroupModel::MEMBER;
            $MainAlias = 'main';
            $MainField = array('uid', 'nickname', 'account', 'pid','total_integral','current_integral');
            //主表模型
            $MainModel = M($MainTab)->alias($MainAlias);
            /*
             * 灵活定义关联查询
             * Ralias   关联表别名
             * Ron      关联条件
             * Rfield   关联查询字段，
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
            //$map['main.pid'] = array('EXP','IS NOT NULL');
            $map['agr.group_id'] = array('eq', 2);            
            /* 查询条件初始化 */
            $map['uc.status'] = array('egt', 0);
            if (!empty($keyword)) {
                $map['main.uid|main.nickname|uc.username'] = array(intval($keyword), array('like', '%' . $keyword . '%'), array('like', '%' . $keyword . '%'), '_multi' => true);
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
                $i=1;
                $mapp[]             ='type=0 and (recharge_type=2 or (recharge_type=1 and pay_status=1))';
                $mappp['type']      = 1;
                $data['total_integral']=M('member_recharge_record')->where($mapp)->sum('integral');

                $data['xiaofei_integral']=M('member_recharge_record')->where($mappp)->sum('integral');
                $data['current_integral']=M('member')->join('left join duoduo_auth_group_access on duoduo_auth_group_access.uid=duoduo_member.uid')->where(array('group_id'=>4))->sum('current_integral');
                foreach ($list as $k => $v) {
                    //数据格式化
                    $info=M('member')->field('uid')->where(array('pid'=>$v['uid']))->select();
                    foreach ($info as $key => $value) {
                        $info[$key] = $value['uid'];
                    }
                    $list[$k]['i']               =$i++;
                    $infos        = implode(',', $info);
                    $infoss['pid'] =array('in',$infos);
                    $infosss=M('member')->field('uid')->where($infoss)->select();
                    foreach ($infosss as $k1 => $val) {
                        $infosss[$k1] = $val['uid'];
                    }
                    $infossss = implode(',', $infosss);                                        
                    $mapp['duoduo_member_recharge_record.mid']   = array('in', $infossss);
                    $list[$k]['total_integral']  =M('member_recharge_record')->where($mapp)->sum('integral');
                    if(empty($list[$k]['total_integral'])){
                        $list[$k]['total_integral']=0;
                    }
                    $mappp['duoduo_member_recharge_record.mid']   = array('in', $infossss);
                    $list[$k]['xiaofei_integral']=M('member_recharge_record')->where($mappp)->sum('integral');
                    $list[$k]['current_integral']=M('member')->where(array('duoduo_member.uid'=>array('in',$infossss)))->sum('current_integral');
                    if(empty($list[$k]['current_integral'])){
                        $list[$k]['current_integral']=0;
                    }
                    $list[$k]['status_text']     = $status_text[$v['status']];
                }
            }
            $this->assign('_list', $list);
            $this->assign('data', $data);
            //操作菜单
            $menuid                             = $this->menuid;
            $SonMenu                            = $this->getSonMenu($menuid);
            $this->assign('ListTopNav', !empty($SonMenu['TOPMENU']) ? $SonMenu['TOPMENU'] : array());
            $this->assign('ListRightNav', !empty($SonMenu['RIGHTMENU']) ? $SonMenu['RIGHTMENU'] : array());

            $this->NavTitle = '用户管理';
            $this->extends_param = '&menuid=' . $this->menuid;
            //记录当前列表页的cookie
            if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf'))
                Cookie('__forward__', $_SERVER['REQUEST_URI']);
            $this->display('index');
        }elseif($group_id==2){
            header("Location:".U('Integral/second','pid='.$uid));die;            

        }elseif($group_id==3){
            header("Location:".U('Integral/sale','pid='.$uid));die;   
        }elseif($group_id==4){
            header("Location:".U('Integral/record','pid='.$uid));die;   
        }

    }


    //二级代理列表
    public function second() {

        $pid  = I('get.pid',0,'intval');
        $type = I('get.type',0,'intval');
        //时间区间检索
        $create_time_s = I('create_time_s', '');
        $create_time_e = I('create_time_e', '');
        if (!empty($create_time_s) && !empty($create_time_e)) {
            $create_stime = $create_time_s . ' 00:00:00';
            $create_etime = $create_time_e . ' 23:59:59';
            $create_time_s = strtotime($create_stime);
            $create_time_e = strtotime($create_etime);
            $mapp['duoduo_member_recharge_record.create_time']  = array(array('egt', $create_time_s), array('elt', $create_time_e));
            $tmapp['duoduo_member_recharge_record.create_time'] = array(array('egt', $create_time_s), array('elt', $create_time_e));
            $mappp['duoduo_member_recharge_record.create_time'] = array(array('egt', $create_time_s), array('elt', $create_time_e));
            $tmappp['duoduo_member_recharge_record.create_time']= array(array('egt', $create_time_s), array('elt', $create_time_e));
        }
        $limit = 20;
        //获取数据
        $MainTab = AuthGroupModel::MEMBER;
        $MainAlias = 'main';
        $MainField = array('uid', 'nickname', 'account', 'pid','total_integral','current_integral');
        //主表模型
        $MainModel = M($MainTab)->alias($MainAlias);
        /*
         * 灵活定义关联查询
         * Ralias   关联表别名
         * Ron      关联条件
         * Rfield   关联查询字段，
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
        $map['main.pid'] = array('eq',$pid);
        $map['agr.group_id'] = array('eq', 3);            
        /* 查询条件初始化 */
        $map['uc.status'] = array('egt', 0);
        if (!empty($keyword)) {
            $map['main.uid|main.nickname|uc.username'] = array(intval($keyword), array('like', '%' . $keyword . '%'), array('like', '%' . $keyword . '%'), '_multi' => true);
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
            $i=1;
            $info=M('member')->field('uid')->where(array('pid'=>$pid))->select();
            foreach ($info as $key => $value) {
                $info[$key] = $value['uid'];
            }
            $infos        = implode(',', $info);
            $infoss['pid'] =array('in',$infos);
            $infosss=M('member')->field('uid')->where($infoss)->select();
            foreach ($infosss as $k1 => $val) {
                $infosss[$k1] = $val['uid'];
            }
            $infossss = implode(',', $infosss);
            $mapp['duoduo_member_recharge_record.mid']   = array('in', $infosss);
            $mapp[]             ='type=0 and (recharge_type=2 or (recharge_type=1 and pay_status=1))';
            $mappp['type']      = 1;
            $mappp['duoduo_member_recharge_record.mid']   = array('in', $infosss);
            $data['total_integral']=M('member_recharge_record')->where($mapp)->sum('integral');

            $data['xiaofei_integral']=M('member_recharge_record')->where($mappp)->sum('integral');
            $data['current_integral']=M('member')->where(array('duoduo_member.uid'=>array('in',$infosss)))->sum('current_integral');
            // print_r($info);exit;
            foreach ($list as $k => $v) {
                //数据格式化
                $list[$k]['i']               =$i++;

                $info=M('member')->field('uid')->where(array('pid'=>$v['uid']))->select();
                foreach ($info as $key => $value) {
                    $info[$key] = $value['uid'];
                }
                $infos        = implode(',', $info); 
                $tmapp['duoduo_member_recharge_record.mid']   = array('in', $infos);
                $tmapp[]             ='type=0 and (recharge_type=2 or (recharge_type=1 and pay_status=1))';  
                $list[$k]['total_integral']  =M('member_recharge_record')->where($tmapp)->sum('integral');
                $tmappp['type']      = 1;
                $tmappp['duoduo_member_recharge_record.mid']   = array('in', $infos);
                $list[$k]['xiaofei_integral']=M('member_recharge_record')->where($tmappp)->sum('integral');
                $list[$k]['current_integral']=M('member')->where(array('duoduo_member.uid'=>array('in',$infos)))->sum('current_integral');
                if(empty($list[$k]['current_integral'])){
                    $list[$k]['current_integral']=0;
                }
                $list[$k]['status_text']     = $status_text[$v['status']];
            }
        }
        $this->assign('_list', $list);
        $this->assign('data', $data);
        // print_r($list);exit;
        //操作菜单
        $this->display();
    
    }
    //商家列表
    public function sale() {

        $pid  = I('get.pid',0,'intval');
        $type = I('get.type',0,'intval');
        //时间区间检索
        $create_time_s = I('create_time_s', '');
        $create_time_e = I('create_time_e', '');
        if (!empty($create_time_s) && !empty($create_time_e)) {
            $create_stime = $create_time_s . ' 00:00:00';
            $create_etime = $create_time_e . ' 23:59:59';
            $create_time_s = strtotime($create_stime);
            $create_time_e = strtotime($create_etime);
            $mapp['duoduo_member_recharge_record.create_time']  = array(array('egt', $create_time_s), array('elt', $create_time_e));
            $tmapp['duoduo_member_recharge_record.create_time'] = array(array('egt', $create_time_s), array('elt', $create_time_e));
            $mappp['duoduo_member_recharge_record.create_time'] = array(array('egt', $create_time_s), array('elt', $create_time_e));
            $tmappp['duoduo_member_recharge_record.create_time']= array(array('egt', $create_time_s), array('elt', $create_time_e));
        }
        $limit = 20;
        //获取数据
        $MainTab = AuthGroupModel::MEMBER;
        $MainAlias = 'main';
        $MainField = array('uid', 'nickname', 'account', 'pid','total_integral','current_integral');
        //主表模型
        $MainModel = M($MainTab)->alias($MainAlias);
        /*
         * 灵活定义关联查询
         * Ralias   关联表别名
         * Ron      关联条件
         * Rfield   关联查询字段，
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
        $map['main.pid'] = array('eq',$pid);
        $map['agr.group_id'] = array('eq', 4);            
        /* 查询条件初始化 */
        $map['uc.status'] = array('egt', 0);
        if (!empty($keyword)) {
            $map['main.uid|main.nickname|uc.username'] = array(intval($keyword), array('like', '%' . $keyword . '%'), array('like', '%' . $keyword . '%'),  '_multi' => true);
        }
        //排序
        $order = $MainAlias . '.uid desc';
        //检索字段
        $fields = (empty($MainField) ? $this->get_fields_string($MainModel->getDbFields(), $MainAlias) . ',' : $this->get_fields_string($MainField, $MainAlias) . ',') . $RelationFields;
        $fields = trim($fields, ',');
        //列表数据
        $list = $this->getLists($model, $map, $order, $fields, $page, $limit, true);
        if (!empty($list)) {
            $i=1;
            $status_text = array('禁用', '正常');
            $info=M('member')->field('uid')->where(array('pid'=>$pid))->select();
            foreach ($info as $key => $value) {
                $info[$key] = $value['uid'];
            }
            $infos        = implode(',', $info);

            $mapp['duoduo_member_recharge_record.mid']   = array('in', $infos);
            $mapp[]             ='type=0 and (recharge_type=2 or (recharge_type=1 and pay_status=1))';
            $mappp['type']      = 1;
            $mappp['duoduo_member_recharge_record.mid']   = array('in', $infos);
            $data['total_integral']=M('member_recharge_record')->where($mapp)->sum('integral');
            if(empty($data['total_integral'])){
                $data['total_integral']=0;
            }
            $data['xiaofei_integral']=M('member_recharge_record')->where($mappp)->sum('integral');
            $data['current_integral']=M('member')->where(array('duoduo_member.uid'=>array('in',$infos)))->sum('current_integral');
            if(empty($data['current_integral'])){
                $data['current_integral']=0;
            }
            foreach ($list as $k => $v) {
                //数据格式化
                $list[$k]['i'] = $i++;
                $tmapp['duoduo_member_recharge_record.mid']   = array('eq', $v['uid']);
                $tmapp[]             ='type=0 and (recharge_type=2 or (recharge_type=1 and pay_status=1))';  
                $list[$k]['total_integral']  =M('member_recharge_record')->where($tmapp)->sum('integral');
                if(empty($list[$k]['total_integral'])){
                    $list[$k]['total_integral']=0;
                }
                $tmappp['type']      = 1;
                $tmappp['duoduo_member_recharge_record.mid']   = array('eq', $v['uid']);
                $list[$k]['xiaofei_integral']=M('member_recharge_record')->where($tmappp)->sum('integral');
                $list[$k]['current_integral']=M('member')->where(array('duoduo_member.uid'=>array('eq',$v['uid'])))->sum('current_integral');;
                if(empty($list[$k]['current_integral'])){
                    $list[$k]['current_integral']=0;
                }
                $list[$k]['status_text'] = $status_text[$v['status']];
            }
        }
        $this->assign('_list', $list);
        $this->assign('data', $data);
        //操作菜单
        $menuid                             = $this->menuid;
        $SonMenu                            = $this->getSonMenu($menuid);
        $this->assign('ListTopNav', !empty($SonMenu['TOPMENU']) ? $SonMenu['TOPMENU'] : array());
        $this->assign('ListRightNav', !empty($SonMenu['RIGHTMENU']) ? $SonMenu['RIGHTMENU'] : array());

        $this->NavTitle = '用户管理';
        $this->extends_param = '&menuid=' . $this->menuid;
        //记录当前列表页的cookie
        if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf'))
            Cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->display();
    
    }


    //商家购买记录列表

    public function record(){
        
        $limit                      = 20;
        $pid                        = I('get.pid',0,'intval');
        //获取数据
        $MainTab                    = 'member_recharge_record';
        $MainAlias                  = 'main';
        $MainField                  = array();

        //主表模型
        $MainModel                  = M($MainTab)->alias($MainAlias);

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
        $map[]                      ='type=0 and (recharge_type=2 or (recharge_type=1 and pay_status=1)) and mid='.$pid;  
        $status                     = intval(I('get.status',0));
        if(!empty($status) && $status > 0){
            $map['recharge_type']      = $status;
        }

        $create_time_s = I('create_time_s', '');
        $create_time_e = I('create_time_e', '');
        if ($create_time_s !== "" && $create_time_e !== "") {
            $create_stime = $create_time_s . ' 00:00:00';
            $create_etime = $create_time_e . ' 23:59:59';
            $create_time_s = strtotime($create_stime);
            $create_time_e = strtotime($create_etime);
            $map['pay_time'] = array(array('egt', $create_time_s), array('elt', $create_time_e));
        } 
        //时间区间检索
        
        //关键词检索
        //排序
        $order                      = $MainAlias.'.id desc';

        //检索字段
        $fields                     = (empty($MainField) ? $this->get_fields_string($MainModel->getDbFields(),$MainAlias).',' : $this->get_fields_string($MainField,$MainAlias).',') . $RelationFields;
        $fields                     = trim($fields,',');

        //列表数据
        $recharge_type              = array('','线上','线下');
        $list                       = $this->getLists($model,$map,$order,$fields,1,$limit,true);
        if (!empty($list)){
            $i=1;
            foreach ($list as $k=>$v){
                //数据格式化
                $list[$k]['i']               = $i++;
                $list[$k]['recharge_type']   = $recharge_type[$v['recharge_type']];
                $list[$k]['pay_time']        = $v['create_time'] > 0 ? date('Y-m-d H:i:s',$v['create_time']) : '--';
            }
        }
        $memberInfo['money'] =M('member_recharge_record')->where($map)->sum('money');
        $memberInfo['integral'] =M('member_recharge_record')->where($map)->sum('integral');   
        $this->assign('memberInfo', $memberInfo);     
        $this->assign('_list', $list);

        //操作菜单,可以根据需要固定$menuid,$menuid为Menu表中的ID
        $menuid                             = $this->menuid;
        $SonMenu                            = $this->getSonMenu($menuid);
        $this->assign('ListTopNav',         !empty($SonMenu['TOPMENU']) ? $SonMenu['TOPMENU'] : array());
        $this->assign('ListRightNav',       !empty($SonMenu['RIGHTMENU']) ? $SonMenu['RIGHTMENU'] : array());

        //代码扩展
        //.........
        //代码扩展

        $this->NavTitle = '配置管理';
        //记录当前列表页的cookie
        if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf')) Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->display();
    }


    //可继续扩展更多类型用户....................

    
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
            array('fieldName' => '店铺头像', 'fieldValue' => 'shopface', 'fieldType' => 'image', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'data-table="member" data-field="shopface" data-size=""'),
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