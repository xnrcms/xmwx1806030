<?php

namespace Admin\Controller;

/**
 * 后台配置控制器
 */
class RefundController extends AdminController {

    /**
     * 修改成自己的
     * @author xxx
     */
    public function index() {
        $line_type=I('get.type', 1);
        $limit = 20;

        //获取数据
        $MainTab = 'refund';
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
        $status=I('status',1);
        $RelationTab             = array(
         'user'=>array('Ralias'=>'ume','Ron'=>'ume ON ume.id=main.uid','Rfield'=>array('id as uuid','nickname','phone')),
         'order_desc'         =>array('Ralias'=>'ord','Ron'=>'ord ON ord.oid=main.oid','Rfield'=>array('gname','gImg','num','avalue','price','points','antcurrency')),
        );
        $RelationTab = $this->getRelationTab($RelationTab);
        $tables = $RelationTab['tables'];
        $RelationFields = $RelationTab['fields'];
        $model = !empty($tables) ? $MainModel->join($tables, 'LEFT') : $MainModel;

        //检索条件
        $map = array();

        //时间区间检索
        $create_time_s = I('create_time_s', '');
        $create_time_e = I('create_time_e', '');
        if ($create_time_s !== "" && $create_time_e !== "") {
            $create_stime = $create_time_s . ' 00:00:00';
            $create_etime = $create_time_e . ' 23:59:59';
            $create_time_s = strtotime($create_stime);
            $create_time_e = strtotime($create_etime);
            $map['main.create_time'] = array(array('egt', $create_time_s), array('elt', $create_time_e));
        }
        //订单编号检索
        //关键词检索
        $keyword = trim(I('find_keyword', ''));
        if (!empty($keyword)) {
            $map['_complex'] = array(
                'ume.nickname' => array('like', '%' . $keyword . '%'),
                'main.phone' => array('like', '%' . $keyword . '%'),
                '_logic' => 'OR',
            );
        }

        //手机号检索
        $map['main.status']=$status;
        //状态检索

        //排序
        $order = $MainAlias . '.id desc';

        //检索字段
        $fields = (empty($MainField) ? $this->get_fields_string($MainModel->getDbFields(), $MainAlias) . ',' : $this->get_fields_string($MainField, $MainAlias) . ',') . $RelationFields;
        $fields = trim($fields, ',');
        $status=array(
            '0'=>'',
            '1'=>'待退款',
            '2'=>'拒绝退款',
            '3'=>'同意退款'
        );
        //列表数据
        $list = $this->getLists($model, $map, $order, $fields, 1, $limit, true);
        
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                //数据格式化
                $list[$k]['status'] = $status[$v['status']];
                $list[$k]['create_time'] = $v['create_time'] > 0 ? date('Y-m-d H:i:s', $v['create_time']) : '--';
                $list[$k]['update_time'] = $v['update_time'] > 0 ? date('Y-m-d H:i:s', $v['update_time']) : '--';
            }
        }
        //******导出excel数据整理start******
        $export_type = I('get.export_type','');
        if($export_type !== ''){
            $info=array();
            foreach($list as $k=>$v){
                $info[$k]['uid']=$v['uid'];
                $info[$k]['id']=$v['id'];
                $info[$k]['nickname']=$v['nickname'];
                $info[$k]['phone']=$v['phone'];
                $info[$k]['num']=$v['num'];
                $info[$k]['money']=$v['money'];
                $info[$k]['status']=$v['status'];
                $info[$k]['create_time']=$v['create_time'];

            }

            $dataResult = $info;
            $title = "订单列表";

            $titlename = "<tr style='text-align: center;'><th style='width:100px;'>用户ID</th><th style='width:110px;'>退款编号</th><th style='width:180px;'>用户昵称</th><th style='width:80px;'>联系方式</th><th style='width:100px;'>购买数量</th><th style='width:80px;'>退款金额</th><th style='width:80px;'>退款状态</th><th style='width:80px;'>申请时间</th></tr>"; 
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
            $cname[] = '订单管理';
            foreach ($ParentCatName as $v) {
                $cname[] = '订单列表';
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
            $this->tupdate();
        //页面数据
        $info = M('refund')
                ->field('duoduo_refund.id,duoduo_refund.money,duoduo_refund.gid,duoduo_refund.oid,duoduo_refund.fmoney,duoduo_refund.express_company,duoduo_refund.express_no,duoduo_refund.phone,duoduo_refund.create_time,duoduo_order_desc.gname,duoduo_order_desc.gImg,duoduo_order_desc.num,duoduo_order_desc.avalue,duoduo_order_desc.price')
                ->join('left join duoduo_order_desc on duoduo_order_desc.oid=duoduo_refund.oid')
                ->where(array('duoduo_refund.id'=>$id))
                ->find();
        $info['create_time'] = $info['create_time'] > 0 ? date('Y-m-d H:i:s', $info['create_time']) : '--';
        if (false === $info) {
            $this->error('获取配置信息错误');
        }
        // print_r($info);exit;
        $this->assign('info', $info);
        //表单数据
        $FormData = $this->CustomerForm(0);
        $this->assign('FormData', $FormData);

        $this->NavTitle = '编辑配置';
        $this->display('addedit');
    }

    /**
     * 编辑数据
     */
    public function view($id = 0) {
        //数据提交
        if (IS_POST)
            $this->tupdate();
        //页面数据
        $info = M('refund')
                ->field('duoduo_refund.id,duoduo_refund.money,duoduo_refund.gid,duoduo_refund.oid,duoduo_refund.fmoney,duoduo_refund.express_company,duoduo_refund.express_no,duoduo_refund.phone,duoduo_refund.create_time,duoduo_order_desc.gname,duoduo_order_desc.gImg,duoduo_order_desc.num,duoduo_order_desc.avalue,duoduo_order_desc.price')
                ->join('left join duoduo_order_desc on duoduo_order_desc.oid=duoduo_refund.oid')
                ->where(array('duoduo_refund.id'=>$id))
                ->find();
        $info['create_time'] = $info['create_time'] > 0 ? date('Y-m-d H:i:s', $info['create_time']) : '--';
        if (false === $info) {
            $this->error('获取配置信息错误');
        }
        // print_r($info);exit;
        $this->assign('info', $info);
        //表单数据
        $FormData = $this->CustomerForm(1);
        $this->assign('FormData', $FormData);

        $this->NavTitle = '编辑配置';
        $this->display();
    }


    //提交表单
    protected function tupdate() {
        if (IS_POST) {
            $status = I('status');
            $status = ltrim($status, ',');
            $status = rtrim($status, ',');
            $id     = I('id');
            $money  = I('money');
            $fail_reason = I('fail_reason');
            $rinfo = M('refund')->field('id,uid,money,doid,oid')->where(array('id' => $id))->find();
            $order = M('order')->field('out_trade_no,total_money')->where(array('id' => $rinfo['oid']))->find();
            if($money>$order['total_money']){
            	$this->error('不能大于订单总金额！');
            }
            
            //拒绝退款
            if ($status == 2) {
                if ($fail_reason == "") {
                    $this->error('拒绝退款时，拒绝理由必填！');
                }
                $ofo = M('refund')->field('id,uid,money,doid,oid')->where(array('id' => $id, 'is_valid' => 1))->find();
                if ($ofo !== false) {
                    $data = array(
                        'refund_id' => $ofo['id'],
                        'uid' => $ofo['uid'],
                        'cid' => UID,
                        'status' => $status,
                        'fail_reason' => $fail_reason,
                        'create_time' => NOW_TIME,
                    );
                    $rid = M('refund_record')->data($data)->add();

                if ($rid > 0) {
                    //给用户推送消息
                    M('refund')->data(array('status'=>2))->where(array('id'=>$id))->save();
                    M('orderDesc')->data(array('is_return'=>2))->where(array('id'=>$ofo['doid']))->save();
                    $this->success('操作成功', Cookie('__forward__'));
                }

                } else {
                    $this->error('操作失败', Cookie('__forward__'));
                }
                //确认退款
            } else {
                //更新订单表
                $info = M('order')->data(array('refunded_time'=>NOW_TIME))->where(array('id' => $rinfo['oid']))->save();
                $ofo = M('refund')->field('id,uid,money,doid')->where(array('id' => $id, 'is_valid' => 1))->find();
                //退买家钱
                //$mid =M('user')->where(array('id' => $ofo['uid']))->setInc('current_money', $ofo['money']);
                //$tmid =M('user')->where(array('id' => $ofo['uid']))->setInc('total_money', $ofo['money']);
                if ($ofo !== false) {
                    $data = array(
                        'refund_id' => $ofo['id'],
                        'uid' => $ofo['uid'],
                        'cid' => UID,
                        'status' => $status,
                        'fail_reason' => $fail_reason,
                        'create_time' => NOW_TIME,
                    );
                    $rid = M('refund_record')->data($data)->add();
                if ($rid > 0) {
                	$reword = array(
                			'out_trade_no' => $order['out_trade_no'],
                			'out_refund_no' => 'TKH'.date('YmdHis',NOW_TIME).randomString('6',0),
                			'total_fee' => $order['total_money'] * 100,
                			'refund_fee' => $money * 100,
                	);
                	$reword_money = R('Home/Weixin/wxRefund', $reword);
                	//if($reword_money['return_code'] == 'SUCCESS'){
                		M('refund')->data(array('status'=>3))->where(array('id'=>$id))->save();
                		M('orderDesc')->data(array('is_return'=>4))->where(array('id'=>$ofo['doid']))->save();
                	//}
                    $this->success('操作成功', Cookie('__forward__'));
                }
                } else {
                    $this->error('退款失败', Cookie('__forward__'));
                }
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


        $FormData[0] = array(
            array('fieldName' => '退款编号', 'fieldValue' => 'id', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '快递公司', 'fieldValue' => 'express_company', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入快递公司"'),
            array('fieldName' => '快递单号', 'fieldValue' => 'express_no', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入快递单号"'),
            array('fieldName' => '联系方式', 'fieldValue' => 'phone', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入联系方式"'),
            array('fieldName' => '订单总金额', 'fieldValue' => 'fmoney', 'fieldType' => 'show', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '商品名称', 'fieldValue' => 'gname', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '商品图片', 'fieldValue' => 'gimg', 'fieldType' => 'image', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '商品属性', 'fieldValue' => 'avalue', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '商品数目', 'fieldValue' => 'num', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '申请时间', 'fieldValue' => 'create_time', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '退款金额', 'fieldValue' => 'money', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '退款类型', 'fieldValue' => 'status', 'fieldType' => 'radio', 'isMust' => 1, 'fieldData' => array(2 => '拒绝退款', 3 => '确认退款'), 'attrExtend' => 'placeholder="" '),
            array('fieldName' => '拒绝理由', 'fieldValue' => 'fail_reason', 'fieldType' => 'text', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="拒绝退款时必填" '),
            array('fieldName' => '隐藏域', 'fieldValue' => array('id'), 'fieldType' => 'hidden', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
        );
        $FormData[1] = array(
            array('fieldName' => '退款编号', 'fieldValue' => 'id', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '快递公司', 'fieldValue' => 'express_company', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入快递公司"'),
            array('fieldName' => '快递单号', 'fieldValue' => 'express_no', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入快递单号"'),
            array('fieldName' => '联系方式', 'fieldValue' => 'phone', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入联系方式"'),
            array('fieldName' => '订单总金额', 'fieldValue' => 'fmoney', 'fieldType' => 'show', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '商品名称', 'fieldValue' => 'gname', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '商品图片', 'fieldValue' => 'gimg', 'fieldType' => 'image', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '商品属性', 'fieldValue' => 'avalue', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '商品数目', 'fieldValue' => 'num', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '申请时间', 'fieldValue' => 'create_time', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '退款金额', 'fieldValue' => 'money', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '隐藏域', 'fieldValue' => array('id'), 'fieldType' => 'hidden', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
        );
        return $FormData[$index];
    }

}

?>