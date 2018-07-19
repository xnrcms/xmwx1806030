<?php
namespace Admin\Controller;
use User\Api\UserApi;
/**
 * 后台配置控制器
 */
class RechargeController extends AdminController {
	/**
     * 用户列表
     */
    public function index($group_id = 0, $parentid = 0) {
        $limit = 20;
        //获取数据
        $MainTab = 'user';
        $MainAlias = 'main';
        $MainField = array('id', 'phone', 'nickname', 'total_coin', 'current_coin', 'total_integral', 'current_integral');
        //主表模型
        $MainModel = M($MainTab)->alias($MainAlias);
        /*
         * 灵活定义关联查询
         * Ralias 	关联表别名
         * Ron    	关联条件
         * Rfield	关联查询字段，
         * */
        $RelationTab = array(
            //AuthGroupModel::AUTH_GROUP_ACCESS => array('Ralias' => 'agr', 'Ron' => 'agr ON main.uid=agr.uid', 'Rfield' => false),
            //AuthGroupModel::UCENTER_MEMBER => array('Ralias' => 'uc', 'Ron' => 'uc ON uc.id=main.uid', 'Rfield' => array('username', 'mobile', 'email', 'status')),
        );

        $RelationTab = $this->getRelationTab($RelationTab);
        $tables = $RelationTab['tables'];
        $RelationFields = $RelationTab['fields'];
        $model = !empty($tables) ? $MainModel->join($tables, 'LEFT') : $MainModel;
        //检索条件
        $keyword = trim(I('get.find_keyword'));
        /* 查询条件初始化 */
        if (!empty($keyword)) {
            $map['main.phone|main.nickname'] = array(array('like', '%' . $keyword . '%'), array('like', '%' . $keyword . '%'), '_multi' => true);
        }else{
        	$uidArr = array(0);
        	$userRechargeRecord = M('userRechargeRecord')->field('uid')->where(array('mid'=>UID))->select();
        	if(!empty($userRechargeRecord)){
        		foreach ($userRechargeRecord as $key=>$value){
        			$uidArr[] = $value['uid'];
        		}
        	}
        	$map['main.id'] = array('in', $uidArr);
        }
        //排序
        $order = $MainAlias . '.id desc';
        //检索字段
        $fields = (empty($MainField) ? $this->get_fields_string($MainModel->getDbFields(), $MainAlias) . ',' : $this->get_fields_string($MainField, $MainAlias) . ',') . $RelationFields;
        $fields = trim($fields, ',');
        //列表数据
        $list = $this->getLists($model, $map, $order, $fields, 1, $limit, true);
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                //数据格式化
                //$list[$k]['status_text'] = $status_text[$v['status']];
            }
        }
        $this->assign('_list', $list);

        //操作菜单
        $menuid = $this->menuid;
        $SonMenu = $this->getSonMenu($menuid);
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
	 * 编辑数据
	 */
	public function edit($id = 0){
		if (IS_POST){
			$currency = I('post.currency', '', 'trim');
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
			//用户
			$memberInfo = M('member')->field('current_currency')->where(array('uid'=>UID))->find();
			if($memberInfo['current_currency']<$currency){
				$this->error('当前积分不足，不能进行充值！');
			}
			$markers = I('post.markers', '', 'trim');
			
			$data = array();
			//商户订单号
			$data['mid'] 			= UID;
	        $data['uid'] 			= $id;
	        $data['currency'] 		= $data['current_currency'] = $data['integral'] = $currency;
	        $data['markers']		= $markers;
	        $data['create_time'] 	= NOW_TIME;
	        
	        
			/* 添加或更新数据 */
			$res = M('userRechargeRecord')->add($data);
			if(false !== $res){
				$data = array();
				$data['total_coin'] 		= array('exp',"total_coin+$currency");
				$data['current_coin'] 		= array('exp',"current_coin+$currency");
				$data['total_integral'] 	= array('exp',"total_integral+$currency");
				$data['current_integral'] 	= array('exp',"current_integral+$currency");
				$res1 = M('user')->where(array('id'=>$id))->save($data); // 根据条件保存修改的数据
				
				$data = array();
				$data['current_currency'] 		= array('exp',"current_currency-$currency");
				$data['current_integral'] 		= array('exp',"current_integral-$currency");
				$res2 = M('member')->where(array('uid'=>UID))->save($data); // 根据条件保存修改的数据
				
				if(false !== $res1 && false !== $res2){
					$row = array();
					//商户订单号
					$row['mid'] 			= UID;
					$row['currency'] 		= $row['integral'] = $currency;
					$row['create_time'] 	= NOW_TIME;
					$row['recharge_type'] 	= 0;	//1商户支付宝购买 2平台给商户充值
					$row['type'] 			= 1;	//0收入 1支出
					/* 添加或更新数据 */
					$res = M('memberRechargeRecord')->add($row);
					//记录行为
					action_log('userRechargeRecord',$res,UID);
					//数据返回
					$this->success('充值成功', Cookie('__forward__'));
				}
			}
			$error = M('userRechargeRecord')->getError();
			$this->error(empty($error) ? '未知错误！' : $error);
		}else{
	        if (empty($id)) $this->error('非法参数！');
	        $info             = M('user')->where(array('id' => $id))->find();
	        $FormData = $this->CustomerForm(0);
	       	$info['id'] 		= $id;
	        $this->assign('info', $info);
	        $this->assign('FormData', $FormData);

	        $this->NavTitle = '编辑';
	        $this->display('edit');
	        
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
		if(M('banner')->where($map)->delete()){
			//记录行为
			action_log('config',$id,UID);
			//数据返回
			$this->success('删除成功',Cookie('__forward__'));
		} else {
			$this->error('删除失败！');
		}
	}

	//提交表单
	protected function update(){
		if(IS_POST){
			$Models 		= D('banner');
			//print_r($_POST);exit();
			//数据整理
			//.......
			//数据整理
			$res 			= $Models->update();
			if(false !== $res){
				//记录行为
				action_log('config',$data['id'],UID);
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
	
	public function currency(){
		if (IS_POST) $this->saveCurrency();
		//表单数据
		$FormData						= $this->CustomerForm(1);
		$this->assign('FormData',       $FormData);
		$this->assign('SmallNav', 			array('配置管理',$this->NavTitle));
		$this->NavTitle 				= '基本配置';
		Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->display('currency');
	}
	
	public function saveCurrency(){
		$money = I('post.money', '', 'trim');
		if($money == ''){
			$this->error('请输入充值金额！');
		}
		if(!preg_match("/^[1-9].0{1,2}$|^[1-9]\d*$/",$money)){
        	$this->error('充值金额必须为大于0的整数！');
        }
        $markers = I('post.markers', '', 'trim');
		$data = array();
		//商户订单号
		$out_trade_no			= 'REWORD'.date('YmdHis',NOW_TIME).randomString('6',0);
        $data['out_trade_no'] 	= $out_trade_no;
        $data['mid'] 			= UID;
        $data['money'] 			= $money;
        $data['currency'] 		= $data['integral'] = $data['money']*10;
        $data['markers']		= $markers;
        $data['create_time'] 	= NOW_TIME;
        $data['recharge_type'] 	= 1;	//1商户支付宝购买 2平台给商户充值
        $data['type'] 			= 0;	//0收入 1支出
        
		if(!$data) return false;
		/* 添加或更新数据 */
		$res = M('memberRechargeRecord')->add($data);
		if(false !== $res){
			//记录行为
			action_log('memberRechargeRecord',$res,UID);
			//数据返回
			$this->success('新增成功', U('Pay/pay',array('out_trade_no'=>$out_trade_no)));
		}
		else
		{
			$error = M('memberRechargeRecord')->getError();
			$this->error(empty($error) ? '未知错误！' : $error);
		}
	}
	

	/*
	 * fieldName	字段名称
	 * fieldValue	字段值
	 * fieldType	字段类型[
	 * 				text		:文本
	 * 				password	:密码
	 * 				checkbox	:复选
	 * 				radio		:单选
	 * 				select		:下拉框
	 * 				textarea	:多行文本
	 * 				editor		:编辑器
	 * 				image		:单图上传
	 * 				images		:多图上传
	 * 				maps		:地图
	 * 				city		:城市选择
	 * 				datetime	:日期格式
	 * 				hidden		:隐藏域
	 * isMust		是否必填
	 * fieldData	字段数据[字段类型为radio,select,checkbox时的列表数据]
	 * Attr			标签属性[常见有:id,class,placeholder,style....]
	 * */
	protected function CustomerForm($index=0){
		$FormData[0] = array(
            array('fieldName' => '用户名', 'fieldValue' => 'phone', 'fieldType' => 'show', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入用户名"'),
            array('fieldName' => '昵称', 'fieldValue' => 'nickname', 'fieldType' => 'show', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入昵称"'),
            array('fieldName' => '积分', 'fieldValue' => 'currency', 'fieldType' => 'text', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入积分"'),
           	array('fieldName' => '确认积分', 'fieldValue' => 'recurrency', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入确认积分"'),
			array('fieldName'=>'备注','fieldValue'=>'markers','fieldType'=>'textarea','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入备注" rows="5" style="height:100%;"'),
            array('fieldName' => '隐藏域', 'fieldValue' => array('id'), 'fieldType' => 'hidden', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
		);
		$FormData[1] = array(
			array('fieldName'=>'充值','fieldValue'=>'money','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入充值金额"'),
			array('fieldName'=>'备注','fieldValue'=>'markers','fieldType'=>'textarea','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入备注" rows="5" style="height:100%;"'),
		);
		return $FormData[$index];
	}
}
?>
