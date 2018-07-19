<?php
namespace Admin\Controller;
/**
 * 优惠券控制器
 */
class CouponController extends AdminController {

	public function index(){
		$limit						= 20;

		//获取数据
		$MainTab					= 'Coupon';
		$MainAlias					= 'main';
		$MainField					= array();

		//主表模型
		$MainModel 					= D($MainTab)->alias($MainAlias);

		/*
		 * 灵活定义关联查询
		 * Ralias 	关联表别名
		 * Ron    	关联条件
		 * Rfield	关联查询字段，
		 * */
		$RelationTab				= $this->getRelationTab($RelationTab);
		$tables	  					= $RelationTab['tables'];
		$RelationFields				= $RelationTab['fields'];
		$model						= !empty($tables) ? $MainModel->join ( $tables ,'LEFT') : $MainModel;

		//检索条件
		$map 						= array();
		
		/* $type						= intval(I('get.type',0));
		if ($type >0){
			$map['type']			= $type;
		} */

		//时间区间检索
		$search_time				= time_between('create_time',$MainAlias,'endTime');
		//关键词检索
		$keyword 					= I('find_keyword','');
		if(!empty($keyword)){
			$map['_complex'] 		= array(
				'parkName' => array('like', '%'.$keyword.'%'),
				'_logic' 	=> 'OR',
			);
		}

		$map						= array_merge($map,$search_time);
		//排序
		$order						= $MainAlias.'.id desc';

		//检索字段
		$fields						= (empty($MainField) ? $this->get_fields_string($MainModel->getDbFields(),$MainAlias).',' : $this->get_fields_string($MainField,$MainAlias).',') . $RelationFields;
		$fields						= trim($fields,',');

		//列表数据
		$list 						= $this->getLists($model,$map,$order,$fields,1,$limit,true);
		
		
		if (!empty($list)){
			foreach ($list as $k=>$v){
				//数据格式化
				
			}
		}
		$this->assign('_list', $list);
		//操作菜单,可以根据需要固定$menuid,$menuid为Menu表中的ID
		$menuid								= $this->menuid;
		$SonMenu							= $this->getSonMenu($menuid);
		$this->assign('ListTopNav', 		!empty($SonMenu['TOPMENU']) ? $SonMenu['TOPMENU'] : array());
		$this->assign('ListRightNav', 		!empty($SonMenu['RIGHTMENU']) ? $SonMenu['RIGHTMENU'] : array());
		
		//代码扩展
		$this->extends_param				.= $this->extends_param;
		//.........
		//代码扩展
		$this->NavTitle = '优惠券管理';
		$this->assign('SmallNav', 			array('优惠券管理','优惠券列表'));
		
		//记录当前列表页的cookie
		if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf')) Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->display();
	}
	
	/**
	 * 新增编辑配置
	 */
	public function addedit($id = 0){
		//数据提交
		if (IS_POST) $this->update();
		
		$info = array();
		if(!empty($id)){
			$info = D('Coupon')->field(true)->where(array('id'=>$id))->find();
			$info['endtime'] = date('Y-m-d', $info['endtime']);
			$this->NavTitle 	= '编辑';
		}else{
			$this->NavTitle 	= '新增';
		}
		$this->assign('info', $info);
		//表单数据
		$FormData						= $this->CustomerForm(0); 
		$this->assign('FormData',       $FormData);
		$this->display();
	}

	//提交表单
	protected function update(){
		if(IS_POST){
			$Models 		= D('Coupon');
			//数据整理
			//.......
			//数据整理
			$res 			= $Models->update();
			if(false !== $res){
				S('DB_CONFIG_DATA',null);
				action_log('coupon',$res['id'],UID);
				//记录行为
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
	 * 删除
	 */
	public function del(){
		$ids			= I('request.ids');
		if ( empty($ids) ) { $this->error('请选择要操作的数据!');}
		$ids 			= is_array($ids) ? $ids : array(intval($ids));
		$ids			= array_unique($ids);
		$map 			= array('id' => array('in', $ids) );
		if(M('Coupon')->where($map)->delete()){
			S('DB_CONFIG_DATA',null);
			//记录行为
			action_log('coupon',$ids,UID);
			$this->success('删除成功',Cookie('__forward__'));
		} else {
			$this->error('删除失败！');
		}
	}

    /**
     * 用户列表
     */
    public function users() {
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
            $map['main.uid|main.nickname|uc.phone'] = array(intval($keyword), array('like', '%' . $keyword . '%'), array('like', '%' . $keyword . '%'), '_multi' => true);
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
                $list[$k]['status']      = $status[$v['status']];
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
        $this->display('users');
    }

    public function send_coupon($id){
    	
    	
		//数据提交
		if (IS_POST) $this->cupdate();
		
		$info = array();
		$info['uid']=$id;
		if(!empty($id)){
			$this->NavTitle 	= '编辑';
		}else{
			$this->NavTitle 	= '新增';
		}
		$this->assign('info', $info);
		//表单数据
		$FormData						= $this->CustomerForm(1); 
		$this->assign('FormData', $FormData);
		$this->display();    	
    }
    
    
    public function sendCoupon(){
    	if (IS_POST) $this->saveCoupon();
    	//表单数据
    	$FormData						= $this->CustomerForm(1);
    	$this->assign('FormData',       $FormData);
    	$this->assign('SmallNav', 			array('发放优惠券',$this->NavTitle));
    	$this->NavTitle 				= '发放优惠券';
    	Cookie('__forward__',$_SERVER['REQUEST_URI']);
    	$this->display();
    }
    
    protected function saveCoupon(){
    	//获取优惠券
    	$couponIdArr = I('couponId','');
    	//获取用户
    	$userIdArr = I('userId','');
    	if(empty($couponIdArr)){
    		$this->error('请选择优惠券！');
    	}
    	if(empty($userIdArr)){
    		$this->error('请选择优惠券发送用户！');
    	}
    	//先判断优惠券库存是否满足
    	foreach ($couponIdArr as $key=>$value){
    		$couponinfo     = M('coupon')->where(array('id'=>$value))->find();
    		if($couponinfo['grantnum']<count($userIdArr)){
    			$this->error($couponinfo['name'].'库存不足！');
    		}
    	}
    	
    	foreach ($couponIdArr as $key=>$value){
    		$couponinfo     = M('coupon')->where(array('id'=>$value))->find();
    		$data = array();
    		foreach ($userIdArr as $k=>$v){
    			$data[] = array(
    				'userid'    => $v,
    				'couponid'  => $value,
    				'isused'    => 0,
    				'usedtime'  => 0,
    				'createtime'=> NOW_TIME,
    				'name'      => $couponinfo['name'],
    				'pic'       => $couponinfo['pic'],
    				'money'     => $couponinfo['money'],
    				'minmoney'  => $couponinfo['minmoney'],
    				'starttime' => $couponinfo['starttime'],
    				'endtime'   => $couponinfo['endtime']
    			);
    			M('coupon')->where(array('id'=>$value))->setDec('grantnum',1);
    		}
    		$res 			= M('couponRecords')->addAll($data);
    	}
    	$this->success($res['ac']>0 ? '发放成功' : '发放成功', Cookie('__forward__'));
    }

	//提交表单
	protected function cupdate(){
		if(IS_POST){
			$id             =I('id','');
			$uid            =I('uid','');
			$couponinfo     =M('coupon')->where(array('id'=>$id))->find();
			if($couponinfo['grantnum']<1){
					$this->error('该优惠券以被发完！');				
			}
			$data=array(
				'userid'    =>$uid,
				'couponid'  =>$id,
				'isused'    =>0,
				'usedtime'  =>0,
				'createtime'=>NOW_TIME,
				'name'      =>$couponinfo['name'],
				'pic'       =>$couponinfo['pic'],
				'money'     =>$couponinfo['money'],
				'minmoney'  =>$couponinfo['minmoney'],
				'starttime' =>$couponinfo['starttime'],
				'endtime'   =>$couponinfo['endtime']

			);
			$Models 		= D('coupon_records');
			$res 			= $Models->data($data)->add();
			if(false !== $res){
				M('coupon')->where(array('id'=>$id))->setDec('grantnum',1);
				action_log('coupon',$res['id'],UID);
				//记录行为
				$this->success($res['ac']>0 ? '更新成功' : '新增成功', Cookie('__forward__'));
			}else{
				$error = $Models->getError();
				$this->error(empty($error) ? '未知错误！' : $error);
			}
		}
		$this->error('非法提交！');
	}



	/*
	 * fieldName	字段名称
	 * fieldValue	字段值
	 * fieldType	字段类型[
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
		$cinfo = M('coupon')->field('id,name')->where(array('endtime'=>array('egt',NOW_TIME)))->select();
		$FormData[0] = array(
			array('fieldName'=>'优惠券名称','fieldValue'=>'name','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入优惠券名称"'),
			//array('fieldName'=>'优惠券图片','fieldValue'=>'pic','fieldType'=>'images','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'data-table="coupon" data-field="pic" data-size=""'),
			array('fieldName'=>'优惠券面额','fieldValue'=>'money','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入优惠券面额"'),
			array('fieldName'=>'最低消费额','fieldValue'=>'minmoney','fieldType'=>'text','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入最低消费额"'),
			array('fieldName'=>'有效期截止时间','fieldValue'=>'endtime','fieldType'=>'datetime','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入有效期截止时间"'),
			array('fieldName'=>'发放张数','fieldValue'=>'grantnum','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入发放张数"'),
			array('fieldName'=>'隐藏域','fieldValue'=>array('id'),'fieldType'=>'hidden','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder=""'),
		);
		
		//获取优惠券
		$coupon = M('coupon')->where(array('endtime'=>array('gt',NOW_TIME), 'grantnum'=>array('gt',0)))->select();
		$couponInfo = array();
		if(!empty($coupon)){
			foreach ($coupon as $key=>$value){
				$info = '';
				if($value['minmoney']>0){
					$info = '(满'.$value['minmoney'].'减'.$value['money'].')';
				}else{
					$info = '(减'.$value['money'].')';
				}
				$couponInfo[$value['id']] = $value['name'].$info;
			}
		}
		
		//获取用户
		$user = M('user')->where(array('status'=>1))->select();
		$userInfo = array();
		if(!empty($user)){
			foreach ($user as $key=>$value){
				$nickname = '';
				if(!empty($value['nickname'])){
					$nickname = '('.$value['nickname'].')';
				}
				$userInfo[$value['id']] = $value['phone'].$nickname;
			}
		}

		$FormData[1] = array(
			array('fieldName' => '优惠券&nbsp;&nbsp;<input type="checkbox" id="selectid_0">全选&nbsp;', 'fieldValue' => 'couponId', 'fieldType' => 'checkbox', 'isMust' => 1, 'fieldData' => $couponInfo, 'attrExtend' => 'placeholder=""'),
			array('fieldName' => '用户&nbsp;&nbsp;<input type="checkbox" id="selectid_1">全选&nbsp;', 'fieldValue' => 'userId', 'fieldType' => 'checkbox', 'isMust' => 1, 'fieldData' => $userInfo, 'attrExtend' => 'placeholder=""'),
		);
		
		return $FormData[$index];
	}
}
?>