<?php

namespace Admin\Controller;

use Admin\Model\AuthGroupModel;
use Think\Model;

/**
 * 后台配置控制器
 */
class AgentController extends AdminController {

    /**
     * 一级代理列表
     * @author xxx
     */
    public function index() {

        $line_type=I('get.type', 1);
        $limit = 20;

        //获取数据
        $MainTab = 'member';
        $MainAlias = 'main';
        $MainField = array();

        //主表模型
        $MainModel = M($MainTab)->alias($MainAlias);

        /*
         * 灵活定义关联查询
         * Ralias 	关联表别名
         * Ron    	关联条件
         * Rfield	关联查询字段，
         * */
        $RelationTab = array(
            'auth_group_access' => array('Ralias' => 'au', 'Ron' => 'au ON au.uid=main.uid', 'Rfield' => array('group_id')),
            'ucenter_member' => array('Ralias' => 'u', 'Ron' => 'u ON u.id=main.uid', 'Rfield' => array('username')),
        );
        $RelationTab = $this->getRelationTab($RelationTab);
        $tables = $RelationTab['tables'];
        $RelationFields = $RelationTab['fields'];
        $model = !empty($tables) ? $MainModel->join($tables, 'LEFT') : $MainModel;

        //检索条件
        $map = array();

        //关键词检索
        $keyword = trim(I('find_keyword', ''));
        if (!empty($keyword)) {
            $map['_complex'] = array(
                'username' => array('like', '%' . $keyword . '%'),
                'main.uid' => array('eq',$keyword),
                '_logic' => 'OR',
            );
        }
        //状态检索
        if (UID != 1) {
            $map['main.pid']    = UID;
        }else{
            $map['au.group_id']  =2;
        }
        //状态检索
        $points_status = intval(I('get.points_status', 0));
        if($points_status==1){
            //排序
            $order = $MainAlias . '.total_integral asc';
        }elseif($points_status==2){
            //排序
            $order = $MainAlias . '.total_integral desc';
        }else{
            //排序
            $order = $MainAlias . '.uid desc';
        }

        //状态检索
        $juyi_status = intval(I('get.juyi_status', 0));
        if($juyi_status==1){
            //排序
            $order = $MainAlias . '.total_currency asc';
        }elseif($juyi_status==2){
            //排序
            $order = $MainAlias . '.total_currency desc';
        }else{
            //排序
            $order = $MainAlias . '.uid desc';
        }

        //状态检索
        $account_status = intval(I('get.account_status', 0));
        if($account_status==1){
            //排序
            $order = $MainAlias . '.account asc';
        }elseif($account_status==2){
            //排序
            $order = $MainAlias . '.account desc';
        }else{
            //排序
            $order = $MainAlias . '.uid desc';
        }

        //检索字段
        $fields = (empty($MainField) ? $this->get_fields_string($MainModel->getDbFields(), $MainAlias) . ',' : $this->get_fields_string($MainField, $MainAlias) . ',') . $RelationFields;
        $fields = trim($fields, ',');

        //列表数据
        $list = $this->getLists($model, $map, $order, $fields, 1, $limit, true);
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                //数据格式化
                $list[$k]['join_time']  = $v['join_time'] > 0 ? date('Y-m-d H:i:s', $v['join_time']) : '--';
                $list[$k]['y_integral'] = $v['total_integral']-$v['current_integral'];
                $list[$k]['y_currency'] = $v['total_currency']-$v['current_currency'];
            }
        }
        //******导出excel数据整理start******
        $export_type = I('get.export_type','');
        if($export_type !== ''){
            $info=array();
            foreach($list as $k=>$v){
                $info[$k]['uid']=$v['uid'];
                $info[$k]['username']=$v['username'];
                $info[$k]['account']=$v['account'];
                $info[$k]['total_integral']=$v['total_integral'];
                $info[$k]['y_integral']=$v['y_integral'];
                $info[$k]['current_integral']=$v['current_integral'];
                $info[$k]['total_currency']=$v['total_currency'];
                $info[$k]['y_currency']=$v['y_currency'];
                $info[$k]['current_currency']=$v['current_currency'];

            }

            $dataResult = $info;
            $title = "代理列表";

            $titlename = "<tr style='text-align: center;'><th style='width:100px;'>用户ID</th><th style='width:110px;'>用户名</th><th style='width:180px;'>账号余额</th><th style='width:80px;'>总积分</th><th style='width:100px;'>已用积分</th><th style='width:80px;'>剩余积分</th><th style='width:80px;'>总聚蚁币</th><th style='width:80px;'>已用聚蚁币</th><th style='width:80px;'>剩余聚蚁币</th></tr>"; 
            $filename = $title.".xls"; 
            $this->excelData($dataResult,$titlename,$headtitle,$filename);
        }
        //******导出excel数据整理end******
        $this->assign('_list', $list);
        $this->assign('line_type', $line_type);
        // print_r($list);exit;
        //操作菜单,可以根据需要固定$menuid,$menuid为Menu表中的ID
        $menuid = $this->menuid;
        $SonMenu = $this->getSonMenu($menuid);
        $this->assign('ListTopNav', !empty($SonMenu['TOPMENU']) ? $SonMenu['TOPMENU'] : array());
        $this->assign('ListRightNav', !empty($SonMenu['RIGHTMENU']) ? $SonMenu['RIGHTMENU'] : array());

        //代码扩展
        $this->extends_param .= $this->extends_param . '&cate_id=' . I('get.cate_id');
        //.........
        //代码扩展

        $this->NavTitle = '配置管理';
        $ParentCatName = D('Category')->getParentName(3, 1);
        if (empty($ParentCatName)) {
            $this->assign('SmallNav', array('商品管理', '商品列表'));
        } else {
            $cname[] = '代理管理';
            foreach ($ParentCatName as $v) {
                $cname[] = '一级代理列表';
            }
            $this->assign('SmallNav', $cname);
        }
        //记录当前列表页的cookie
        if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf'))
            Cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->display();
    }


    /**
     * 二级代理列表
     * @author xxx
     */
    public function sindex() {

        $line_type=I('get.type', 1);
        $limit = 20;

        //获取数据
        $MainTab = 'member';
        $MainAlias = 'main';
        $MainField = array();

        //主表模型
        $MainModel = M($MainTab)->alias($MainAlias);

        /*
         * 灵活定义关联查询
         * Ralias   关联表别名
         * Ron      关联条件
         * Rfield   关联查询字段，
         * */
        $RelationTab = array(
            'auth_group_access' => array('Ralias' => 'au', 'Ron' => 'au ON au.uid=main.uid', 'Rfield' => array('group_id')),
            'ucenter_member' => array('Ralias' => 'u', 'Ron' => 'u ON u.id=main.uid', 'Rfield' => array('username')),
        );
        $RelationTab = $this->getRelationTab($RelationTab);
        $tables = $RelationTab['tables'];
        $RelationFields = $RelationTab['fields'];
        $model = !empty($tables) ? $MainModel->join($tables, 'LEFT') : $MainModel;

        //检索条件
        $map = array();
        //关键词检索
        $keyword = trim(I('find_keyword', ''));
        if (!empty($keyword)) {
            $map['_complex'] = array(
                'username' => array('like', '%' . $keyword . '%'),
                'main.uid' => array('eq',$keyword),
                '_logic' => 'OR',
            );
        }
        if (UID != 1) {
            $map['main.pid']    = UID;
        }else{
            $map['au.group_id']  =3;
        }
        //排序
        //状态检索
        $points_status = intval(I('get.points_status', 0));
        if($points_status==1){
            //排序
            $order = $MainAlias . '.total_integral asc';
        }elseif($points_status==2){
            //排序
            $order = $MainAlias . '.total_integral desc';
        }else{
            //排序
            $order = $MainAlias . '.uid desc';
        }

        //状态检索
        $juyi_status = intval(I('get.juyi_status', 0));
        if($juyi_status==1){
            //排序
            $order = $MainAlias . '.total_currency asc';
        }elseif($juyi_status==2){
            //排序
            $order = $MainAlias . '.total_currency desc';
        }else{
            //排序
            $order = $MainAlias . '.uid desc';
        }

        //状态检索
        $account_status = intval(I('get.account_status', 0));
        if($account_status==1){
            //排序
            $order = $MainAlias . '.account asc';
        }elseif($account_status==2){
            //排序
            $order = $MainAlias . '.account desc';
        }else{
            //排序
            $order = $MainAlias . '.uid desc';
        }

        //检索字段
        $fields = (empty($MainField) ? $this->get_fields_string($MainModel->getDbFields(), $MainAlias) . ',' : $this->get_fields_string($MainField, $MainAlias) . ',') . $RelationFields;
        $fields = trim($fields, ',');

        //列表数据
        $list = $this->getLists($model, $map, $order, $fields, 1, $limit, true);
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                //数据格式化
                $list[$k]['join_time']  = $v['join_time'] > 0 ? date('Y-m-d H:i:s', $v['join_time']) : '--';
                $list[$k]['y_integral'] = $v['total_integral']-$v['current_integral'];
                $list[$k]['y_currency'] = $v['total_currency']-$v['current_currency'];
            }
        }
        //******导出excel数据整理start******
        $export_type = I('get.export_type','');
        if($export_type !== ''){
            $info=array();
            foreach($list as $k=>$v){
                $info[$k]['uid']=$v['uid'];
                $info[$k]['username']=$v['username'];
                $info[$k]['account']=$v['account'];
                $info[$k]['total_integral']=$v['total_integral'];
                $info[$k]['y_integral']=$v['y_integral'];
                $info[$k]['current_integral']=$v['current_integral'];
                $info[$k]['total_currency']=$v['total_currency'];
                $info[$k]['y_currency']=$v['y_currency'];
                $info[$k]['current_currency']=$v['current_currency'];

            }

            $dataResult = $info;
            $title = "代理列表";

            $titlename = "<tr style='text-align: center;'><th style='width:100px;'>用户ID</th><th style='width:110px;'>用户名</th><th style='width:180px;'>账号余额</th><th style='width:80px;'>总积分</th><th style='width:100px;'>已用积分</th><th style='width:80px;'>剩余积分</th><th style='width:80px;'>总聚蚁币</th><th style='width:80px;'>已用聚蚁币</th><th style='width:80px;'>剩余聚蚁币</th></tr>"; 
            $filename = $title.".xls"; 
            $this->excelData($dataResult,$titlename,$headtitle,$filename);
        }
        //******导出excel数据整理end******
        $this->assign('_list', $list);
        $this->assign('line_type', $line_type);
        //操作菜单,可以根据需要固定$menuid,$menuid为Menu表中的ID
        $menuid = $this->menuid;
        $SonMenu = $this->getSonMenu($menuid);
        $this->assign('ListTopNav', !empty($SonMenu['TOPMENU']) ? $SonMenu['TOPMENU'] : array());
        $this->assign('ListRightNav', !empty($SonMenu['RIGHTMENU']) ? $SonMenu['RIGHTMENU'] : array());

        //代码扩展
        $this->extends_param .= $this->extends_param . '&cate_id=' . I('get.cate_id');
        //.........
        //代码扩展

        $this->NavTitle = '配置管理';
        $ParentCatName = D('Category')->getParentName(3, 1);
        if (empty($ParentCatName)) {
            $this->assign('SmallNav', array('商品管理', '商品列表'));
        } else {
            $cname[] = '代理管理';
            foreach ($ParentCatName as $v) {
                $cname[] = '代理列表';
            }
            $this->assign('SmallNav', $cname);
        }
        //记录当前列表页的cookie
        if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf'))
            Cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->display();
    }


    /**
     * 商家列表
     * @author xxx
     */
    public function sale_index() {

        $line_type=I('get.type', 1);
        $limit = 20;


        /*
         * 灵活定义关联查询
         * Ralias   关联表别名
         * Ron      关联条件
         * Rfield   关联查询字段，
         * */

        //检索条件
        $map = array();

        //关键词检索
        $keyword = trim(I('find_keyword', ''));
        if (!empty($keyword)) {
            $map['_complex'] = array(
                'username' => array('like', '%' . $keyword . '%'),
                'duoduo_member.uid' => array('eq',$keyword),
                '_logic' => 'OR',
            );
        }
        if (UID != 1) {
            $group_id=M('auth_group_access')->where(array('uid'=>UID))->getField('group_id');
            if($group_id==2){
                $info=M('member')->field('uid')->where(array('pid'=>UID))->select();
                foreach ($info as $key => $value) {
                    $info[$key] = $value['uid'];
                }

                $info = implode(',', $info);
                $map['duoduo_member.pid'] = array('in', $info);
                
            }else{
                $map['duoduo_member.pid']=UID;
            }
        }else{
               $map['group_id']=4;
        }
        //排序
        //状态检索
        $points_status = intval(I('get.points_status', 0));
        if($points_status==1){
            //排序
            $order ='duoduo_member.total_integral asc';
        }elseif($points_status==2){
            //排序
            $order ='duoduo_member.total_integral desc';
        }else{
            //排序
            $order ='duoduo_member.uid desc';
        }

        //状态检索
        $juyi_status = intval(I('get.juyi_status', 0));
        if($juyi_status==1){
            //排序
            $order = 'duoduo_member.total_currency asc';
        }elseif($juyi_status==2){
            //排序
            $order ='duoduo_member.total_currency desc';
        }else{
            //排序
            $order ='duoduo_member.uid desc';
        }

        //状态检索
        $account_status = intval(I('get.account_status', 0));
        if($account_status==1){
            //排序
            $order ='duoduo_member.account asc';
        }elseif($account_status==2){
            //排序
            $order ='duoduo_member.account desc';
        }else{
            //排序
            $order ='duoduo_member.uid desc';
        }

        //列表数据
        $list =M('member')->field('duoduo_member.*,duoduo_ucenter_member.username,duoduo_ucenter_member.id')->join('left join duoduo_ucenter_member on duoduo_ucenter_member.id=duoduo_member.uid')->join('left join duoduo_auth_group_access on duoduo_auth_group_access.uid=duoduo_member.uid')->order('duoduo_member.uid desc')->where($map)->select();
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                //数据格式化
                $list[$k]['join_time']  = $v['join_time'] > 0 ? date('Y-m-d H:i:s', $v['join_time']) : '--';
                $list[$k]['y_integral'] = $v['total_integral']-$v['current_integral'];
                $list[$k]['y_currency'] = $v['total_currency']-$v['current_currency'];
                $list[$k]['tusername']  = M('ucenter_member')->where(array('id'=>$v['pid']))->getField('username');
                $list[$k]['tpid']  = M('member')->where(array('uid'=>$v['pid']))->getField('pid');
                $list[$k]['yusername']  = M('ucenter_member')->where(array('id'=> $list[$k]['tpid']))->getField('username');                
            }
        }

        //******导出excel数据整理start******
        $export_type = I('get.export_type','');
        if($export_type !== ''){
            $info=array();
            foreach($list as $k=>$v){
                $info[$k]['uid']=$v['uid'];
                $info[$k]['username']=$v['username'];
                $info[$k]['yusername']=$v['yusername'];
                $info[$k]['tusername']=$v['tusername'];
                $info[$k]['account']=$v['account'];
                $info[$k]['total_integral']=$v['total_integral'];
                $info[$k]['y_integral']=$v['y_integral'];
                $info[$k]['current_integral']=$v['current_integral'];
                $info[$k]['total_currency']=$v['total_currency'];
                $info[$k]['y_currency']=$v['y_currency'];
                $info[$k]['current_currency']=$v['current_currency'];

            }

            $dataResult = $info;
            $title = "商家列表";

            $titlename = "<tr style='text-align: center;'><th style='width:100px;'>用户ID</th><th style='width:110px;'>用户名</th><th style='width:110px;'>一级代理</th><th style='width:110px;'>二级代理</th><th style='width:180px;'>账号余额</th><th style='width:80px;'>总积分</th><th style='width:100px;'>已用积分</th><th style='width:80px;'>剩余积分</th><th style='width:80px;'>总聚蚁币</th><th style='width:80px;'>已用聚蚁币</th><th style='width:80px;'>剩余聚蚁币</th></tr>"; 
            $filename = $title.".xls"; 
            $this->excelData($dataResult,$titlename,$headtitle,$filename);
        }
        //******导出excel数据整理end******
        $this->assign('_list', $list);
        $this->assign('line_type', $line_type);
        //操作菜单,可以根据需要固定$menuid,$menuid为Menu表中的ID
        $menuid = $this->menuid;
        $SonMenu = $this->getSonMenu($menuid);
        $this->assign('ListTopNav', !empty($SonMenu['TOPMENU']) ? $SonMenu['TOPMENU'] : array());
        $this->assign('ListRightNav', !empty($SonMenu['RIGHTMENU']) ? $SonMenu['RIGHTMENU'] : array());

        //代码扩展
        $this->extends_param .= $this->extends_param . '&cate_id=' . I('get.cate_id');
        //.........
        //代码扩展

        $this->NavTitle = '配置管理';
        if (empty($ParentCatName)) {
            $this->assign('SmallNav', array('商家管理', '商家列表'));
        } else {
            $cname[] = '商家管理';
            foreach ($ParentCatName as $v) {
                $cname[] = '商家列表';
            }
            $this->assign('SmallNav', $cname);
        }
        //记录当前列表页的cookie
        if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf'))
            Cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->display();
    }

    /**
     * 编辑数据
     */
    public function edit($id = 0) {
        //数据提交
        if (IS_POST)
            $this->update();
        //页面数据
        $info['uid']=$id;
        if (false === $info) {
            $this->error('获取配置信息错误');
        }
        $this->assign('info', $info);

        //表单数据
        $FormData = $this->CustomerForm(3);
        $this->assign('FormData', $FormData);

        $this->NavTitle = '编辑配置';
        $this->display('addedit');
    }

    //提交表单
    protected function update() {

        if (IS_POST) {
            $id     = I('id');
            $uid    = I('uid');
            $Models = D('member');
            //print_r($_POST);exit();
            //数据整理
            if($id==0){
                $this->error('请选择二级代理'); 
            }
            //数据整理
            $res =M('member')->data(array('pid'=>$id))->where(array('uid'=>$uid))->save();
            if (false !== $res) {
                //记录行为
                action_log('config', $data['id'], UID);
                //数据返回
                $this->success($res['ac'] > 0 ? '更新成功' : '新增成功', Cookie('__forward__'));
            } else {
                $error = $Models->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        }
        $this->error('非法提交！');
    }

    /* 
    *处理Excel导出 
    *@param $datas array 设置表格数据 
    *@param $titlename string 设置head 
    *@param $title string 设置表头 
    */ 
    public function excelData($datas,$titlename,$title,$filename){ 
        $str = "<html xmlns:o=\"urn:schemas-microsoft-com:office:office\"\r\nxmlns:x=\"urn:schemas-microsoft-com:office:excel\"\r\nxmlns=\"http://www.w3.org/TR/REC-html40\">\r\n<head>\r\n<meta http-equiv=Content-Type content=\"text/html; charset=utf-8\">\r\n</head>\r\n<body>"; 
        $str .="<table border=1>".$titlename; 
        $str .= $title; 

        foreach ($datas  as $key=> $rt ) 
        { 
            $str .= "<tr style='text-align: center;'>"; 
            foreach ( $rt as $k => $v ) 
            { 
                $str .= "<td>{$v}</td>"; 
            } 
            $str .= "</tr>\n"; 
        } 
        $str .= "</table></body></html>"; 
        // print_r($str);exit;
        header( "Content-Type: application/vnd.ms-excel; name='excel'" ); 
        header( "Content-type: application/octet-stream" ); 
        header( "Content-Disposition: attachment; filename=".$filename ); 
        header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" ); 
        header( "Pragma: no-cache" ); 
        header( "Expires: 0" ); 
        exit( $str ); 
    }

    /*
     * fieldName    字段名称
     * fieldValue   字段值
     * fieldType    字段类型[
     *              text        :文本
     *              password    :密码
     *              checkbox    :复选
     *              radio       :单选
     *              select      :下拉框
     *              textarea    :多行文本
     *              editor      :编辑器
     *              image       :单图上传
     *              images      :多图上传
     *              maps        :地图
     *              city        :城市选择
     *              datetime    :日期格式
     *              hidden      :隐藏域
     * isMust       是否必填
     * fieldData    字段数据[字段类型为radio,select,checkbox时的列表数据]
     * Attr         标签属性[常见有:id,class,placeholder,style....]
     * */

    protected function CustomerForm($index = 0) {
        $pidData=M('ucenter_member')->field('duoduo_ucenter_member.id,duoduo_ucenter_member.username as name')->join('left join duoduo_auth_group_access on duoduo_auth_group_access.uid=duoduo_ucenter_member.id')->where(array('duoduo_auth_group_access.group_id'=>3))->select();

        $pidData = array_merge(array(0=>array('id'=>0,'name'=>'-请选择-')), $pidData);
        $FormData[3] = array(
            array('fieldName' => '二级代理', 'fieldValue' => 'id', 'fieldType' => 'select2', 'isMust' => 1, 'fieldData' => $pidData, 'attrExtend' => 'placeholder=""'),
            array('fieldName'=>'隐藏域','fieldValue'=>array('uid'),'fieldType'=>'hidden','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder=""'),
        );
        return $FormData[$index];
    } 

}

?>