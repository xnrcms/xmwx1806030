<?php

namespace Admin\Controller;

/**
 * 后台配置控制器
 */
class MallFinanceController extends AdminController {

    /**
     * 修改成自己的
     * @author xxx
     */
    public function index() {
    	
    	//商家所在的用户组
    	$usergroup = $this->usergroup;
    	$usergroup = array_values($usergroup['gid']);
    	$usergroup = $usergroup[0];
    	$this->assign('usergroup', $usergroup);
    	
        $line_type=I('get.type', 1);
        $export_type = I('get.export_type','');
        if($export_type !== ''){
        	$limit = 5000;
        }else{
        	$limit = 20;
        }

        //获取数据
        $MainTab = 'order';
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
        // $RelationTab             = array(
        //  'ucenter_member'=>array('Ralias'=>'ume','Ron'=>'ume ON ume.id=main.uid','Rfield'=>array('id as uuid','')),
        // );
        $RelationTab = $this->getRelationTab($RelationTab);
        $tables = $RelationTab['tables'];
        $RelationFields = $RelationTab['fields'];
        $model = !empty($tables) ? $MainModel->join($tables, 'LEFT') : $MainModel;

        //检索条件
        $map 				= array();
        $map['pay_status']	= 1;

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
        $phone = trim(I('phone', ''));
        if (!empty($order_no)) {
            $map['_complex'] = array(
                'phone' => array('like', '%' . $phone . '%'),
                '_logic' => 'OR',
            );
        }

        //手机号检索
        $uid = trim(I('uid', ''));
        if (!empty($uid)) {
            $map['main.uid']=$uid;
        }

        //状态检索
        $status = intval(I('get.status', 0));
        if (!empty($status) && $status > 0) {
            $map['status'] = $status;
        }
        
        //排序
        $order = $MainAlias . '.id desc';

        //检索字段
        $fields = (empty($MainField) ? $this->get_fields_string($MainModel->getDbFields(), $MainAlias) . ',' : $this->get_fields_string($MainField, $MainAlias) . ',') . $RelationFields;
        $fields = trim($fields, ',');

        //列表数据
        $status = array(
            0 => '',
            1 => '待付款',
            2 => '待发货',
            3 => '待收货',
            4 => '已完成',
            5 => '待退款',
            6 => '拒绝退款',
            7 => '已退款',
            8 => '已评价',
        	9 => '已取消'
        );
        $pay_status = array(
            0 => '未支付',
            1 => '已支付',
        );
        $list = $this->getLists($model, $map, $order, $fields, 1, $limit, true);
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                //数据格式化
            	$list[$k]['phone'] = M('user')->where(array('id'=>$v['uid']))->getField('phone');
                $list[$k]['pay_time'] = $v['pay_time'] > 0 ? date('Y-m-d H:i:s', $v['pay_time']) : '--';
            }
        }
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

        $this->NavTitle = '财务管理';
        $ParentCatName = D('Category')->getParentName(3, 1);
        if (empty($ParentCatName)) {
            $this->assign('SmallNav', array('财务管理', '财务列表'));
        } else {
            $cname[] = '财务管理';
            foreach ($ParentCatName as $v) {
                $cname[] = '财务列表';
            }
            $this->assign('SmallNav', $cname);
        }
        //记录当前列表页的cookie
        if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf'))
            Cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->display();
    }

    /**
     * 订单详情页
     */
    public function view() {
        $id = I('get.id');
        $info = M('Order')->field(true)->find($id);
        $status = array(
            0 => '',
            1 => '待付款',
            2 => '待发货',
            3 => '待收货',
            4 => '已完成',
            5 => '待退款',
            6 => '拒绝退款',
            7 => '已退款',
            8 => '已取消'
        );
        $pay_status = array(
            0 => '未支付',
            1 => '已支付',
        );
        if ($info['status'] == 5 || $info['status'] == 6 || $info['status'] == 7) {
            $refund = M('refund')->where(array('oid' => $info['id'], 'is_valid' => 1))->find();
            $refund['original_status'] = $status[$refund['original_status']];
            $refund['create_time'] = $refund['create_time'] > 0 ? date('Y-m-d H:i:s', $refund['create_time']) : '--';
        }
        $info['state'] = $info['status'];
        $info['status'] = $status[$info['status']];
        $info['pay_status'] = $pay_status[$info['pay_status']];
        $info['create_time'] = $info['create_time'] > 0 ? date('Y-m-d H:i:s', $info['create_time']) : '--';
        $info['glist'] = M('orderDesc')
                ->field('duoduo_order_desc.*')
                ->where(array('oid'=>$id))
                ->select();
        // print_r($info);exit;
        $this->assign('info', $info);
        $this->assign('refund', $refund);
        $this->display();
    }

    //商城财务统计
    public function orderdata(){
        //时间区间检索
        $create_time_s = I('create_time_s', '');
        $create_time_e = I('create_time_e', '');
        if ($create_time_s !== "" && $create_time_e !== "") {
        	$create_stime = $create_time_s . ' 00:00:00';
        	$create_etime = $create_time_e . ' 23:59:59';
        	$create_time_s = strtotime($create_stime);
        	$create_time_e = strtotime($create_etime);
        	
        	$beginYesterday		= $create_time_s;
        	$endYesterday		= $create_time_e;
        }else{
        	$beginYesterday		= mktime(0,0,0,date('m'),date('d')-7,date('Y'));
        	$endYesterday		= mktime(23,59,59,date('m'),date('d'),date('Y'));
        }
        
        //昨日成功下单人数
        $map['create_time'] = array(array('egt', $beginYesterday), array('elt', $endYesterday));        
        $info['data1']=M('order')->where($map)->group('duoduo_order.uid')->count();        
        //昨日成单数量
        $cmap['confirmed_time'] = array(array('egt', $beginYesterday), array('elt', $endYesterday)); 
        $cmap['status'] = array('eq',4);        
        $info['data2']=M('order')->where($cmap)->count();  
        //昨日成功销售额      
        $info['data3']=M('order')->where($cmap)->sum('total_money');
        if(empty($info['data3'])){
            $info['data3']='0.00';
        }
        //昨日已支付订单量
        $pmap['pay_time'] = array(array('egt', $beginYesterday), array('elt', $endYesterday)); 
        $pmap['status'] = array('eq',2);        
        $info['data4']=M('order')->where($pmap)->count();  
        //昨日待支付订单量
        $dmap['create_time'] = array(array('egt', $beginYesterday), array('elt', $endYesterday));   
        $dmap['status'] = array('eq',1);       
        $info['data5']=M('order')->where($dmap)->count();   
        //昨日订单退货量
        $tmap['create_time'] = array(array('egt', $beginYesterday), array('elt', $endYesterday));        
        $info['data6']=M('refund')->where($tmap)->count(); 
        $this->assign('info',$info);
        $this->assign('SmallNav', array('财务统计', '统计列表'));
        $this->display();
    }

    /**
     * 编辑数据
     */
    public function edit($id = 0) {
        //数据提交
        //页面数据
        $info = M('Order')->field(true)->find($id);
        $info['create_time'] = $info['create_time'] > 0 ? date('Y-m-d H:i:s', $info['create_time']) : '--';
        if ($info['status'] == 2) {
            if (IS_POST){
            	$_POST['send_time'] = NOW_TIME;
            	$this->update();
            }else{
            	$info['status'] = '待发货';
            	$info['pay_status'] = '已支付';
            	if (false === $info) {
            		$this->error('获取配置信息错误');
            	}
            	$this->assign('info', $info);
            	//表单数据
            	$FormData = $this->CustomerForm(0);
            	$this->assign('FormData', $FormData);
            	
            	$this->NavTitle = '编辑配置';
            	$this->display('addedit');
            }
        } elseif($info['status'] == 1){
            if (IS_POST)
                $this->update();
            $info['status'] = '待付款';
            $info['pay_status'] = '未支付';
            if (false === $info) {
                $this->error('获取配置信息错误');
            }
            if(empty($info['express_company'])){
                $info['express_company']="暂无快递公司";
            }
            if(empty($info['express_no'])){
                $info['express_no']="暂无快递单号";
            }
        	$this->assign('info', $info);

	        //表单数据
	        $FormData = $this->CustomerForm(3);
	        $this->assign('FormData', $FormData);
	
	        $this->NavTitle = '编辑配置';
	        $this->display('addedit');
        }elseif ($info['status'] == 5) {
            if(empty($info['express_company'])){
                $info['express_company']="暂无快递公司";
            }
            if(empty($info['express_no'])){
                $info['express_no']="暂无快递单号";
            }
            $info['status'] = '待发货';
            $info['pay_status'] = '已支付';
            $info['money'] = M('refund')->where(array('oid' => $id,'is_valid'=>1))->sum('money');
            if (IS_POST)
                $this->tupdate();
            if (false === $info) {
                $this->error('获取配置信息错误');
            }
            $this->assign('info', $info);

            //表单数据
            $FormData = $this->CustomerForm(1);
            $this->assign('FormData', $FormData);

            $this->NavTitle = '编辑配置';
            $this->display('taddedit');
        } else {
            //列表数据
            $status = array(
                0 => '',
                1 => '待付款',
                2 => '待发货',
                3 => '待收货',
                4 => '已完成',
                5 => '待退款',
                6 => '拒绝退款',
                7 => '已退款',
                8 => '已取消'
            );
            $pay_status = array(
                0 => '未支付',
                1 => '已支付',
            );

            if(empty($info['express_company'])){
                $info['express_company']="暂无快递公司";
            }
            if(empty($info['express_no'])){
                $info['express_no']="暂无快递单号";
            }
            $info['status'] = $status[$info['status']];
            $info['pay_status'] = $pay_status[$info['pay_status']];
            if (false === $info) {
                $this->error('获取配置信息错误');
            }
            $this->assign('info', $info);

            //表单数据
            $FormData = $this->CustomerForm(2);
            $this->assign('FormData', $FormData);

            $this->NavTitle = '编辑配置';
            $this->display('qaddedit');
        }
    }

    /**
     * 删除数据
     */
    public function del() {
        $ids = I('request.ids');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        $ids = is_array($ids) ? $ids : array(intval($ids));
        $ids = array_unique($ids);
        $map = array('id' => array('in', $ids));
        if (M('Order')->where($map)->delete()) {
            //记录行为
            action_log('config', $id, UID);
            //数据返回
            $this->success('删除成功', Cookie('__forward__'));
        } else {
            $this->error('删除失败！');
        }
    }

    //提交表单
    protected function update() {
        if (IS_POST) {
            $Models = D('Order');
            //数据整理
            $res = $Models->update();
            if (false !== $res) {
                //记录行为
                $list = M('order')->where(array('id' => $_POST['id']))->find();
                $data = array(
                    'oid' => $list['id'],
                    'cid' => UID,
                    'status' => 3,
                    'last_status' => $list['status'],
                    'create_time' => NOW_TIME,
                );
                    M('order_record')->data($data)->add();

                //数据返回
                $this->success($res['ac'] > 0 ? '更新成功' : '新增成功', Cookie('__forward__'));
            } else {
                $error = $Models->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        }
        $this->error('非法提交！');
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
            $Models = M('order');
            $orderinfo = M('order')->where(array('id' => $id))->find();
            $rinfo = M('refund')->field('id,uid,money')->where(array('oid' => $id,'uid'=>$orderinfo['uid']))->find();
            //拒绝退款
            if ($status == 6) {
                if ($fail_reason == "") {
                    $this->error('拒绝退款时，拒绝理由必填！');
                }
                $info = M('order')->data(array('status' =>6))->where(array('id' => $id))->save();
                $ofo = M('refund')->field('id,uid,money')->where(array('oid' => $id, 'is_valid' => 1))->select();
                if ($info !== false) {
                    foreach ($ofo as $key => $value) {
                    $data = array(
                        'refund_id' => $value['id'],
                        'uid' => $value['uid'],
                        'cid' => UID,
                        'status' => $status,
                        'fail_reason' => $fail_reason,
                        'create_time' => NOW_TIME,
                    );
                    $rid = M('refund_record')->data($data)->add();

                    }
                if ($rid > 0) {
                //给用户推送消息
                    $this->success('操作成功', Cookie('__forward__'));
                }

                } else {
                    $this->error('操作失败', Cookie('__forward__'));
                }
                //确认退款
            } else {
                //更新订单表
                $info = M('order')->data(array('status' => $status,'refunded_time'=>NOW_TIME))->where(array('id' => $id))->save();
                $ofo = M('refund')->field('id,uid,money')->where(array('oid' => $id, 'is_valid' => 1))->select();
                $money = M('refund')->where(array('oid' => $id, 'is_valid' => 1))->sum('money');
                //退买家钱
                $mid =M('user')->where(array('id' => $orderinfo['uid']))->setInc('current_money', $money);
                $mid =M('user')->where(array('id' => $orderinfo['uid']))->setInc('total_money', $money);
                if ($mid !== false) {
                    foreach ($ofo as $k => $val) {
                    $data = array(
                        'refund_id' => $val['id'],
                        'uid' => $val['uid'],
                        'cid' => UID,
                        'status' => $status,
                        'fail_reason' => $fail_reason,
                        'create_time' => NOW_TIME,
                    );
                    $rid     = M('refund_record')->data($data)->add();
                    $descinfo=M('order_desc')->data(array('is_return'=>1))->where(array('id'=>$ofo['doid']))->save();
                }
                if ($rid > 0) {
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
                $str .= "<td style='vnd.ms-excel.numberformat:@'>{$v}</td>";
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
     * 确认兑换
     */
    public function exchange(){
    	$id = I('get.ids',0,'intval');
    	if (empty($id)) {
    		$this->error('请选择要操作的数据!');
    	}
    	$result = M('Order')->where(array('id'=>$id))->save(array('member_id'=>UID,'exchange_time'=>NOW_TIME));
    	if (false !== $result) {
    		//数据返回
    		$this->success('兑换成功', Cookie('__forward__'));
    	} else {
    		$this->error('兑换失败！');
    	}
    }
    
    /*
     * 商家积分兑换列表
     */
    public function exchangeList(){
    	//商家所在的用户组
    	$usergroup = $this->usergroup;
    	$usergroup = array_values($usergroup['gid']);
    	$usergroup = $usergroup[0];
    	$this->assign('usergroup', $usergroup);
    	 
    	$line_type=I('get.type', 1);
    	//是否导出
    	$export = I('get.export',0,'intval');
    	if($export == 1){
    		$limit = 5000;
    	}else{
    		$limit = 20;
    	}
    	
    	//获取数据
    	$MainTab = 'order';
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
    	$RelationTab             = array(
    		//'order_desc'=>array('Ralias'=>'o_d','Ron'=>'o_d ON o_d.oid=main.id','Rfield'=>array('gname','num','avalue')),
    		'member'=>array('Ralias'=>'m','Ron'=>'m ON m.uid = main.member_id','Rfield'=>array('nickname')),
    		'ucenter_member'=>array('Ralias'=>'u_m','Ron'=>'u_m ON u_m.id = main.member_id','Rfield'=>array('username as m_username')),
    	);
    	$RelationTab = $this->getRelationTab($RelationTab);
    	$tables = $RelationTab['tables'];
    	$RelationFields = $RelationTab['fields'];
    	$model = !empty($tables) ? $MainModel->join($tables, 'LEFT') : $MainModel;
    	
    	//检索条件
    	$map = array();
    	if($usergroup == 2){
    		$ids = M('member')->field('uid')->where(array('pid'=>UID))->select();
    		$idss = array('-1');
    		foreach ($ids as $key=>$value){
    			$idss[] = $value['uid'];
    		}
    		$idsss = M('member')->field('uid')->where(array('pid'=>array('in',$idss)))->select();
    		$idssss = array('-1');
    		foreach ($idsss as $key=>$value){
    			$idssss[] = $value['uid'];
    		}
    		$map['main.member_id']		= array('in',$idssss);
    	}elseif($usergroup == 3){
    		$ids = M('member')->field('uid')->where(array('pid'=>UID))->select();
    		$idss = array('-1');
    		foreach ($ids as $key=>$value){
    			$idss[] = $value['uid'];
    		}
    		$map['main.member_id']		= array('in',$idss);
    	}elseif($usergroup == 4){
    		$ids = UID;
    		$map['main.member_id']		= array('in',$ids);
    	}else{
    		$map['main.member_id'] = array('gt',0);
    	}
    	
    	/* $platformId = I('platformId',0,'intval');
    	if(empty($platformId)){
    		$platformId = $this->userInfo['platform_id'];
    	}
    	$ids = D('Platform')->getChildrenId($platformId);
    	$map['main.platformId']		= array('in',$ids); */
    	
    	//时间区间检索
    	$create_time_s = I('create_time_s', '');
    	$create_time_e = I('create_time_e', '');
    	if ($create_time_s !== "" && $create_time_e !== "") {
    		$create_stime = $create_time_s . ' 00:00:00';
    		$create_etime = $create_time_e . ' 23:59:59';
    		$create_time_s = strtotime($create_stime);
    		$create_time_e = strtotime($create_etime);
    		$map['main.exchange_time'] = array(array('egt', $create_time_s), array('elt', $create_time_e));
    	}
    	//订单编号检索
    	/* $order_no = trim(I('order_no', ''));
    	if (!empty($order_no)) {
    		$map['_complex'] = array(
    				'order_no' => array('like', '%' . $order_no . '%'),
    				'_logic' => 'OR',
    		);
    	} */
    	
    	//商家用户名检索
    	$m_username = trim(I('m_username', '', 'trim'));
    	if (!empty($m_username)) {
    		$map['u_m.username'] = array('like', '%' . $m_username . '%');
    	}
    	
    	//商家名称检索
    	$nickname = trim(I('nickname', '', 'trim'));
    	if (!empty($nickname)) {
    		$map['m.nickname'] = array('like', '%' .  $nickname . '%');
    	}
    	
    	//排序
    	$order = $MainAlias . '.id desc';
    	
    	//检索字段
    	$fields = (empty($MainField) ? $this->get_fields_string($MainModel->getDbFields(), $MainAlias) . ',' : $this->get_fields_string($MainField, $MainAlias) . ',') . $RelationFields;
    	$fields = trim($fields, ',');
    	
    	$list = $this->getLists($model, $map, $order, $fields, 1, $limit, true);
    	if (!empty($list)) {
    		foreach ($list as $k => $v) {
    			//商家用户名
    			$list[$k]['m_username'] = $v['m_username'];
    			//商家名称
    			$list[$k]['nickname'] = $v['nickname'];
    			
    			$orderDesc = M('orderDesc')->where(array('oid'=>$v['id']))->select();
    			$list[$k]['goodsInfo'] = '';
    			foreach ($orderDesc as $key=>$value){
    				$list[$k]['goodsInfo'] .= $value['gname'].'('.$value['avalue'].') 数量x'.$value['num'].'<br>';
    			}
    			//数据格式化
    			$list[$k]['exchange_time'] = $v['exchange_time'] > 0 ? date('Y-m-d H:i:s', $v['exchange_time']) : '--';
    		}
    	}
    	
    	//p($list);die;
    	
    	//******导出excel数据整理start******
    	if($export == 1){
    		$info=array();
    		foreach($list as $k=>$v){
    			$info[$k]['id']				= $v['id'];
    			$info[$k]['order_no']		= $v['order_no'];
    			$info[$k]['m_username']		= $v['m_username'];
    			$info[$k]['nickname']		= $v['nickname'];
    			$info[$k]['goodsInfo']		= $v['goodsInfo'];
    			$info[$k]['total_integral']	= $v['total_integral'];
    			$info[$k]['username']		= $v['username'];
    			$info[$k]['exchange_time']	= $v['exchange_time'];
    		}
    		$dataResult = $info;
    		$title = "商家积分兑换列表";
    		$titlename = "<tr style='text-align: center;'><th style='width:100px;'>订单ID</th><th style='width:200px;'>订单编号</th><th style='width:100px;'>商家用户名</th><th style='width:100px;'>商家名称</th><th style='width:200px;'>商品信息</th><th style='width:80px;'>积分</th><th style='width:200px;'>兑换账户</th><th style='width:100px;'>兑换时间</th></tr>";
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
    	
    	$this->NavTitle = '商家积分兑换管理';
    	$this->assign('SmallNav', array('商家积分兑换管理', '商家积分兑换列表'));
    	//记录当前列表页的cookie
    	if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf'))
    		Cookie('__forward__', $_SERVER['REQUEST_URI']);
    		$this->display();
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
            array('fieldName' => '订单编号', 'fieldValue' => 'order_no', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '省', 'fieldValue' => 'province', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入省"'),
            array('fieldName' => '市', 'fieldValue' => 'city', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入市"'),
            array('fieldName' => '区', 'fieldValue' => 'county', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入区"'),
            array('fieldName' => '详细地址', 'fieldValue' => 'address', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入详细地址"'),
            array('fieldName' => '收件人', 'fieldValue' => 'rname', 'fieldType' => 'text', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入收件人姓名"'),
            array('fieldName' => '联系方式', 'fieldValue' => 'phone', 'fieldType' => 'text', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入收件人联系方式"'),
            array('fieldName' => '订单总额', 'fieldValue' => 'total_money', 'fieldType' => 'text', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入订单总价"'),
            array('fieldName' => '商品数目', 'fieldValue' => 'gnum', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '订单状态', 'fieldValue' => 'status', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="" '),
            array('fieldName' => '支付状态', 'fieldValue' => 'pay_status', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '下单时间', 'fieldValue' => 'create_time', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '快递公司', 'fieldValue' => 'express_company', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入快递公司"'),
            array('fieldName' => '快递单号', 'fieldValue' => 'express_no', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入快递单号"'),
            array('fieldName' => '隐藏域', 'fieldValue' => array('id'), 'fieldType' => 'hidden', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
        );
        $FormData[1] = array(
            array('fieldName' => '订单编号', 'fieldValue' => 'order_no', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '省', 'fieldValue' => 'province', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入省"'),
            array('fieldName' => '市', 'fieldValue' => 'city', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入市"'),
            array('fieldName' => '区', 'fieldValue' => 'county', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入区"'),
            array('fieldName' => '详细地址', 'fieldValue' => 'address', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入详细地址"'),
            array('fieldName' => '收件人', 'fieldValue' => 'rname', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入收件人姓名"'),
            array('fieldName' => '联系方式', 'fieldValue' => 'phone', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入收件人联系方式"'),
            array('fieldName' => '订单总额', 'fieldValue' => 'total_money', 'fieldType' => 'text', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入订单总价"'),
            array('fieldName' => '退款金额', 'fieldValue' => 'money', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '商品数目', 'fieldValue' => 'gnum', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '订单状态', 'fieldValue' => 'status', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="" '),
            array('fieldName' => '支付状态', 'fieldValue' => 'pay_status', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '快递公司', 'fieldValue' => 'express_company', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入快递公司"'),
            array('fieldName' => '快递单号', 'fieldValue' => 'express_no', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入快递单号"'),
            array('fieldName' => '下单时间', 'fieldValue' => 'create_time', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '退款类型', 'fieldValue' => 'cstatus', 'fieldType' => 'radio', 'isMust' => 1, 'fieldData' => array(6 => '拒绝退款', 7 => '确认退款'), 'attrExtend' => 'placeholder="" '),
            array('fieldName' => '拒绝理由', 'fieldValue' => 'fail_reason', 'fieldType' => 'text', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="拒绝退款时必填" '),
            array('fieldName' => '隐藏域', 'fieldValue' => array('id'), 'fieldType' => 'hidden', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
        );
        $FormData[2] = array(
            array('fieldName' => '订单编号', 'fieldValue' => 'order_no', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '省', 'fieldValue' => 'province', 'fieldType' => 'show', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入省"'),
            array('fieldName' => '市', 'fieldValue' => 'city', 'fieldType' => 'show', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入市"'),
            array('fieldName' => '区', 'fieldValue' => 'county', 'fieldType' => 'show', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入区"'),
            array('fieldName' => '详细地址', 'fieldValue' => 'address', 'fieldType' => 'show', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入详细地址"'),
            array('fieldName' => '收件人', 'fieldValue' => 'rname', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入收件人姓名"'),
            array('fieldName' => '联系方式', 'fieldValue' => 'phone', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入收件人联系方式"'),
            array('fieldName' => '订单总额', 'fieldValue' => 'total_money', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入订单总价"'),
            array('fieldName' => '商品数目', 'fieldValue' => 'gnum', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '订单状态', 'fieldValue' => 'status', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="" '),
            array('fieldName' => '支付状态', 'fieldValue' => 'pay_status', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '下单时间', 'fieldValue' => 'create_time', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '快递公司', 'fieldValue' => 'express_company', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入快递公司"'),
            array('fieldName' => '快递单号', 'fieldValue' => 'express_no', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入快递单号"'),
            array('fieldName' => '隐藏域', 'fieldValue' => array('id'), 'fieldType' => 'hidden', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
        );


        $FormData[3] = array(
            array('fieldName' => '订单编号', 'fieldValue' => 'order_no', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '省', 'fieldValue' => 'province', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入省"'),
            array('fieldName' => '市', 'fieldValue' => 'city', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入市"'),
            array('fieldName' => '区', 'fieldValue' => 'county', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入区"'),
            array('fieldName' => '详细地址', 'fieldValue' => 'address', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入详细地址"'),
            array('fieldName' => '收件人', 'fieldValue' => 'rname', 'fieldType' => 'text', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入收件人姓名"'),
            array('fieldName' => '联系方式', 'fieldValue' => 'phone', 'fieldType' => 'text', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入收件人联系方式"'),
            array('fieldName' => '订单总额', 'fieldValue' => 'total_money', 'fieldType' => 'text', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入订单总价"'),
            array('fieldName' => '商品数目', 'fieldValue' => 'gnum', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '订单状态', 'fieldValue' => 'status', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="" '),
            array('fieldName' => '支付状态', 'fieldValue' => 'pay_status', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '下单时间', 'fieldValue' => 'create_time', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '快递公司', 'fieldValue' => 'express_company', 'fieldType' => 'show', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入快递公司"'),
            array('fieldName' => '快递单号', 'fieldValue' => 'express_no', 'fieldType' => 'show', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入快递单号"'),
            array('fieldName' => '隐藏域', 'fieldValue' => array('id'), 'fieldType' => 'hidden', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
        );
        return $FormData[$index];
    }

}

?>