<?php
namespace Admin\Controller;
/**
 * 后台配置控制器
 */
class MemberFinanceController extends AdminController {
	/**
     * 聚蚁币充值记录
     */
    public function currency() {
        $limit = 20;
        //获取数据
        $MainTab = 'memberRechargeRecord';
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
            //AuthGroupModel::AUTH_GROUP_ACCESS => array('Ralias' => 'agr', 'Ron' => 'agr ON main.uid=agr.uid', 'Rfield' => false),
            //AuthGroupModel::UCENTER_MEMBER => array('Ralias' => 'uc', 'Ron' => 'uc ON uc.id=main.uid', 'Rfield' => array('username', 'mobile', 'email', 'status')),
        );

        $RelationTab = $this->getRelationTab($RelationTab);
        $tables = $RelationTab['tables'];
        $RelationFields = $RelationTab['fields'];
        $model = !empty($tables) ? $MainModel->join($tables, 'LEFT') : $MainModel;
        //检索条件
        $map = array();
        $map['mid'] = UID;
        $keyword = trim(I('get.find_keyword'));
        /* 查询条件初始化 */
        if (!empty($keyword)) {
            $map['main.out_trade_no'] = array(array('like', '%' . $keyword . '%'));
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
        
        //用户
        $memberInfo = M('member')->field('total_currency, current_currency, total_integral, current_integral')->where(array('uid'=>UID))->find();
        $this->assign('memberInfo', $memberInfo);
        
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
        $this->display('currency');
    }

    /**
     * 会员充值记录
     */
    public function user() {
    	$limit = 20;
    	//获取数据
    	$MainTab = 'userRechargeRecord';
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
    			'user' => array('Ralias' => 'u', 'Ron' => 'u ON main.uid=u.id', 'Rfield' => array('phone', 'nickname')),
    			//AuthGroupModel::UCENTER_MEMBER => array('Ralias' => 'uc', 'Ron' => 'uc ON uc.id=main.uid', 'Rfield' => array('username', 'mobile', 'email', 'status')),
    	);
    
    	$RelationTab = $this->getRelationTab($RelationTab);
    	$tables = $RelationTab['tables'];
    	$RelationFields = $RelationTab['fields'];
    	$model = !empty($tables) ? $MainModel->join($tables, 'LEFT') : $MainModel;
    	//检索条件
    	$map = array();
    	$map['main.mid'] = UID;
    	$keyword = trim(I('get.find_keyword'));
    	/* 查询条件初始化 */
    	if (!empty($keyword)) {
    		$map['u.phone'] = array(array('like', '%' . $keyword . '%'));
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
    		$this->display('user');
    }
    
	/**
	 * 编辑数据
	 */
	public function edit($id = 0){
		if (IS_POST){
			$currency = I('post.currency', '0', 'intval');
			if(empty($currency)){
				$this->error('请输入聚蚁币');
			}
			$data = array();
			//商户订单号
			$data['mid'] 			= UID;
	        $data['uid'] 			= $id;
	        $data['currency'] 		= $data['current_currency'] = $data['integral'] = $currency;
	        $data['create_time'] 	= NOW_TIME;
			if(!$data) return false;
			/* 添加或更新数据 */
			$res = M('userMemberFinanceRecord')->add($data);
			if(false !== $res){
				$data = array();
				$data['total_coin'] 		= array('exp',"total_coin+$currency");
				$data['current_coin'] 		= array('exp',"current_coin+$currency");
				$data['total_integral'] 	= array('exp',"total_integral+$currency");
				$data['current_integral'] 	= array('exp',"current_integral+$currency");
				$res1 = M('user')->where(array('id'=>$id))->save($data); // 根据条件保存修改的数据
				if(false !== $res1){
					//记录行为
					action_log('userMemberFinanceRecord',$res,UID);
					//数据返回
					$this->success('充值成功', Cookie('__forward__'));
				}
			}
			$error = M('userMemberFinanceRecord')->getError();
			$this->error(empty($error) ? '未知错误！' : $error);
		}else{
	        if (empty($id)) $this->error('非法参数！');
	        $FormData = $this->CustomerForm(0);
	        $this->assign('FormData', $FormData);
	        $info['id'] 		= $id;
	        $this->assign('info', $info);
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
	
	
	public function saveCurrency(){
		$data = array();
		//商户订单号
		$out_trade_no			= 'REWORD'.date('YmdHis',NOW_TIME).randomString('6',0);
        $data['out_trade_no'] 	= $out_trade_no;
        $data['mid'] 			= UID;
        $data['money'] 			= I('post.money', '', 'trim');
        $data['currency'] 		= $data['integral'] = $data['money']*100;
        $data['create_time'] 	= NOW_TIME;
        
		if(!$data) return false;
		/* 添加或更新数据 */
		$res = M('memberMemberFinanceRecord')->add($data);
		if(false !== $res){
			//记录行为
			action_log('memberMemberFinanceRecord',$res,UID);
			//数据返回
			$this->success('新增成功', U('Pay/pay',array('out_trade_no'=>$out_trade_no)));
		}
		else
		{
			$error = M('memberMemberFinanceRecord')->getError();
			$this->error(empty($error) ? '未知错误！' : $error);
		}
	}
	
	/**
	 * 银行账号
	 */
	public function card() {
		$FormData = $this->CustomerForm(2);
		$this->assign('FormData', $FormData);
		$info = M('bankCard')->field(true)->find();
		$this->assign('info', $info);
		$this->NavTitle = '银行账号设置';
		$this->display();
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
            array('fieldName' => '聚蚁币', 'fieldValue' => 'currency', 'fieldType' => 'text', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入聚蚁币"'),
            array('fieldName' => '隐藏域', 'fieldValue' => array('id'), 'fieldType' => 'hidden', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
		);
		$FormData[1] = array(
			array('fieldName'=>'充值','fieldValue'=>'money','fieldType'=>'text','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入充值金额"'),	
		);
		//银行账号
		$FormData[2] = array(
			array('fieldName' => '开户银行', 'fieldValue' => 'bank_name', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入开户银行"'),
			array('fieldName' => '开户账号', 'fieldValue' => 'card_number', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入开户账号"'),
			array('fieldName' => '开户名称', 'fieldValue' => 'user_name', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入开户名称"'),
		);
		return $FormData[$index];
	}
}
?>
