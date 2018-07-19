<?php
namespace Admin\Controller;
/**
 * 后台配置控制器
 */
class CommissionController extends AdminController {
	/**
	 * 修改成自己的
	 * @author xxx
	 */
	public function index(){
		$limit						= 20;

		//获取数据
		$MainTab					= 'commission';
		$MainAlias					= 'main';
		$MainField					= array();

		//主表模型
		$MainModel 					= M($MainTab)->alias($MainAlias);

		/*
		 * 灵活定义关联查询
		 * Ralias 	关联表别名
		 * Ron    	关联条件
		 * Rfield	关联查询字段，
		 * */
		$RelationTab				= array(
		//'member'=>array('Ralias'=>'me','Ron'=>'me ON me.uid=main.uid','Rfield'=>array('uid as uuid','nickname')),
		);
		$RelationTab				= $this->getRelationTab($RelationTab);
		$tables	  					= $RelationTab['tables'];
		$RelationFields				= $RelationTab['fields'];
		$model						= !empty($tables) ? $MainModel->join ( $tables ,'LEFT') : $MainModel;

		//检索条件
		$map 						= array();

		//时间区间检索
		$create_time				= time_between('create_time',$MainAlias);
		
		//关键词检索
		$keyword 					= I('find_keyword','');
		if(!empty($keyword)){
			$map['_complex'] 		= array(
				'text' => array('like', '%'.$keyword.'%'),
				'uid' 	=> array('eq', $keyword),
				'_logic' 	=> 'OR',
			);
		}
		
		//状态检索
		$status 				= intval(I('get.find_status',0));
		if(!empty($status) && $status > 0){
			$map['status'] 		= $status;
		}

		$map						= array_merge($map,$create_time);
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
				$list[$k]['create_time']	= $v['create_time'] > 0 ? date('Y-m-d H:i:s',$v['create_time']) : '--';
				$list[$k]['update_time']	= $v['update_time'] > 0 ? date('Y-m-d H:i:s',$v['update_time']) : '--';
			}
		}
		$this->assign('_list', $list);

		//操作菜单,可以根据需要固定$menuid,$menuid为Menu表中的ID
		$menuid								= $this->menuid;
		$SonMenu							= $this->getSonMenu($menuid);
		$this->assign('ListTopNav', 		!empty($SonMenu['TOPMENU']) ? $SonMenu['TOPMENU'] : array());
		$this->assign('ListRightNav', 		!empty($SonMenu['RIGHTMENU']) ? $SonMenu['RIGHTMENU'] : array());

		//代码扩展
		//.........
		//代码扩展

		$this->NavTitle = '配置管理';
		//记录当前列表页的cookie
		if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf')) Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->display();
	}


	public function rebate(){
		$limit						= 20;

		//获取数据
		$MainTab					= 'rebate';
		$MainAlias					= 'main';
		$MainField					= array();

		//主表模型
		$MainModel 					= M($MainTab)->alias($MainAlias);

		/*
		 * 灵活定义关联查询
		 * Ralias 	关联表别名
		 * Ron    	关联条件
		 * Rfield	关联查询字段，
		 * */
		$RelationTab				= array(
		//'member'=>array('Ralias'=>'me','Ron'=>'me ON me.uid=main.uid','Rfield'=>array('uid as uuid','nickname')),
		);
		$RelationTab				= $this->getRelationTab($RelationTab);
		$tables	  					= $RelationTab['tables'];
		$RelationFields				= $RelationTab['fields'];
		$model						= !empty($tables) ? $MainModel->join ( $tables ,'LEFT') : $MainModel;

		//检索条件
		$map 						= array();

		//时间区间检索
		$create_time				= time_between('create_time',$MainAlias);
		
		//关键词检索
		$keyword 					= I('find_keyword','');
		if(!empty($keyword)){
			$map['_complex'] 		= array(
				'text' => array('like', '%'.$keyword.'%'),
				'uid' 	=> array('eq', $keyword),
				'_logic' 	=> 'OR',
			);
		}
		
		//状态检索
		$status 				= intval(I('get.find_status',0));
		if(!empty($status) && $status > 0){
			$map['status'] 		= $status;
		}

		$map						= array_merge($map,$create_time);
		//排序
		$order						= $MainAlias.'.id desc';

		//检索字段
		$fields						= (empty($MainField) ? $this->get_fields_string($MainModel->getDbFields(),$MainAlias).',' : $this->get_fields_string($MainField,$MainAlias).',') . $RelationFields;
		$fields						= trim($fields,',');

		//列表数据
		$list 						= $this->getLists($model,$map,$order,$fields,1,$limit,true);
		$status=array(
			'1'=>'支付成功',
			'2'=>'订单完成',
			);
		if (!empty($list)){
			foreach ($list as $k=>$v){
				//数据格式化
				$list[$k]['status']         =$status[$v['status']];
				$list[$k]['create_time']	= $v['create_time'] > 0 ? date('Y-m-d H:i:s',$v['create_time']) : '--';
				$list[$k]['update_time']	= $v['update_time'] > 0 ? date('Y-m-d H:i:s',$v['update_time']) : '--';
			}
		}
		$this->assign('_list', $list);


		//操作菜单,可以根据需要固定$menuid,$menuid为Menu表中的ID
		$menuid								= $this->menuid;
		$SonMenu							= $this->getSonMenu($menuid);
		$this->assign('ListTopNav', 		!empty($SonMenu['TOPMENU']) ? $SonMenu['TOPMENU'] : array());
		$this->assign('ListRightNav', 		!empty($SonMenu['RIGHTMENU']) ? $SonMenu['RIGHTMENU'] : array());

		//代码扩展
		//.........
		//代码扩展

		$this->NavTitle = '配置管理';
		//记录当前列表页的cookie
		if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf')) Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->display();
	}

	/**
	 * 新增数据
	 */
	public function add(){
		//数据提交
		if (IS_POST) $this->update();

		//页面数据
		$info 							= array();
		$info['isseletc']				= 2;
		$this->assign('info',$info);

		//表单数据
		$FormData						= $this->CustomerForm(0);
		$this->assign('FormData',       $FormData);

		$this->NavTitle = '新增配置';
		$this->display('addedit');
	}

	/**
	 * 编辑数据
	 */
	public function edit($id = 0){
		//数据提交
		if (IS_POST) $this->update();

		//页面数据
		$info 			= M('commission')->field(true)->find($id);
		if(false === $info){
			$this->error('获取配置信息错误');
		}
		$this->assign('info', $info);

		//表单数据
		$FormData						= $this->CustomerForm(0);
		$this->assign('FormData',       $FormData);

		$this->NavTitle = '编辑配置';
		$this->display('addedit');
	}

	/**
	 * 编辑数据
	 */
	public function user_edit($id = 0){
		//数据提交

		//页面数据
		$info 			= M('member')
						  ->join('left join duoduo_ucenter_member on duoduo_ucenter_member.id=duoduo_member.uid')
						  ->where(array('uid'=>$id))
						  ->field('duoduo_member.*,duoduo_ucenter_member.username,duoduo_ucenter_member.mobile')
						  ->find();
		if(false === $info){
			$this->error('获取配置信息错误');
		}
		$this->assign('info', $info);

		//表单数据
		$FormData						= $this->CustomerForm(2);
		$this->assign('FormData',       $FormData);

		$this->NavTitle = '编辑配置';
		$this->display('edit');
	}

	/**
	 * 编辑数据
	 */
	public function user_list($id = 0){
		//数据提交
		if (IS_POST) $this->update();

		//页面数据
		$list 			= M('member')
						  ->join('left join duoduo_ucenter_member on duoduo_ucenter_member.id=duoduo_member.uid')
						  ->where(array('recommentor'=>$id))
						  ->field('duoduo_member.*,duoduo_ucenter_member.username,duoduo_ucenter_member.mobile')
						  ->select();

		//表单数据
		$this->assign('_list', $list);
		$this->display();
	}

	/**
	 * 编辑数据
	 */
	public function rebate_edit($id = 0){

		//数据提交
		if (IS_POST) $this->rebate_update();

		//页面数据
		$info 			= M('rebate')->field(true)->find($id);
		if(false === $info){
			$this->error('获取配置信息错误');
		}
		$this->assign('info', $info);

		//表单数据
		$FormData						= $this->CustomerForm(1);
		$this->assign('FormData',       $FormData);

		$this->NavTitle = '编辑配置';
		$this->display('rebateaddedit');
	}


	/**
	 * 删除数据
	 */
	public function rebate_del(){
		$ids			= I('request.ids');
		if ( empty($ids) ) { $this->error('请选择要操作的数据!');}
		$ids 			= is_array($ids) ? $ids : array(intval($ids));
		$ids			= array_unique($ids);
		$map 			= array('id' => array('in', $ids) );
		if(M('rebate')->where($map)->delete()){
			//记录行为
			action_log('config',$id,UID);
			//数据返回
			$this->success('删除成功',Cookie('__forward__'));
		} else {
			$this->error('删除失败！');
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
		if(M('commission')->where($map)->delete()){
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
			$Models 		= D('commission');
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

	//提交表单
	protected function rebate_update(){
		if(IS_POST){
			$Models 		= D('rebate');
			$data['first_rate']   =$_POST['first_rate'];
			$data['free_rate']    =$_POST['first_rate'];
			$data['new_rate']     =$_POST['new_rate'];
			$data['status']       =$_POST['status'];
			$data['id']           =$_POST['id'];
			$res=M('rebate')->data($data)->where(array('id'=>$data['id']))->save();
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


    /**
     * 分销人员列表
     */
    public function sale_agency($group_id = 0, $parentid = 0) {
        $limit = 20;
        //获取数据
        $MainTab = "member";
        $MainAlias = 'main';
        $MainField = array('uid', 'nickname', 'last_login_time', 'last_login_ip', 'account', 'login');
        //主表模型
        $MainModel = M($MainTab)->alias($MainAlias);
        /*
         * 灵活定义关联查询
         * Ralias 	关联表别名
         * Ron    	关联条件
         * Rfield	关联查询字段，
         * */
        $RelationTab = array(
            "auth_group_access" => array('Ralias' => 'agr', 'Ron' => 'agr ON main.uid=agr.uid', 'Rfield' => false),
            "ucenter_member"=> array('Ralias' => 'uc', 'Ron' => 'uc ON uc.id=main.uid', 'Rfield' => array('username', 'mobile', 'email', 'status')),
        );

        $RelationTab = $this->getRelationTab($RelationTab);
        $tables = $RelationTab['tables'];
        $RelationFields = $RelationTab['fields'];
        $model = !empty($tables) ? $MainModel->join($tables, 'LEFT') : $MainModel;
        //检索条件
        $keyword = trim(I('get.find_keyword'));
        /* 查询条件初始化 */
        $map['uc.status'] = array('egt', 0);
        $map['agr.group_id']=array('eq',2);
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
                $recommentor=M('member')->where(array('uid'=>$v['uid']))->getField('recommentor');
                $list[$k]['snickname']=M('member')->join('left join duoduo_ucenter_member on duoduo_ucenter_member.id=duoduo_member.uid')->where(array('uid'=>$recommentor))->getField('username');
                $list[$k]['sid']=M('member')->join('left join duoduo_ucenter_member on duoduo_ucenter_member.id=duoduo_member.uid')->where(array('uid'=>$recommentor))->getField('uid');
                $list[$k]['xnickname']=M('member')->join('left join duoduo_ucenter_member on duoduo_ucenter_member.id=duoduo_member.uid')->where(array('recommentor'=>$v['id']))->getField('username');
                $list[$k]['xid']=M('member')->join('left join duoduo_ucenter_member on duoduo_ucenter_member.id=duoduo_member.uid')->where(array('recommentor'=>$v['id']))->getField('uid');
				$list[$k]['num']=$this->getChildrenList($v['uid']);
				if($list[$k]['num']<1){
					unset($list[$k]);
				}
            }
        }

        $this->assign('_list', $list);
        //操作菜单
        $SonMenu = $this->getSonMenu(5);
        $this->assign('ListTopNav', !empty($SonMenu['TOPMENU']) ? $SonMenu['TOPMENU'] : array());
        $this->assign('ListRightNav', !empty($SonMenu['RIGHTMENU']) ? $SonMenu['RIGHTMENU'] : array());

        $this->NavTitle = '用户管理';
        $this->extends_param = '&menuid=' . $this->menuid;
        //记录当前列表页的cookie
        if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf'))
            Cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->display();
    }

    public function getChildrenList($id){
        $list       = M('member')->where("recommentor='$id'")->count();
        return $list;
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
		array('fieldName'=>'一级分销佣金比例(%)','fieldValue'=>'one_rate','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入一级分销佣金比例"'),
		array('fieldName'=>'二级分销佣金比例(%)','fieldValue'=>'two_rate','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入二级分销佣金比例"'),
		array('fieldName'=>'隐藏域','fieldValue'=>array('id'),'fieldType'=>'hidden','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder=""'),
		);

		$FormData[1] = array(
		array('fieldName'=>'首单专区返利(%)','fieldValue'=>'first_rate','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入首单专区返利比例"'),
		array('fieldName'=>'免费试用返利(%)','fieldValue'=>'free_rate','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入免费试用返利比例"'),
		array('fieldName'=>'新品专区返利(%)','fieldValue'=>'new_rate','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入新品专区返利比例"'),
		array('fieldName'=>'返利发放时间','fieldValue'=>'status','fieldType'=>'radio','isMust'=>1,'fieldData'=>array(1=>'支付成功',2=>'订单完成'),'attrExtend'=>'placeholder="请选择返利发放时间"'),
		array('fieldName'=>'隐藏域','fieldValue'=>array('id'),'fieldType'=>'hidden','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder=""'),
		);
        //资料修改
        $FormData[2] = array(
            array('fieldName' => '用户昵称', 'fieldValue' => 'username', 'fieldType' => 'show', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '用户手机', 'fieldValue' => 'mobile', 'fieldType' => 'show', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '用户余额', 'fieldValue' => 'account', 'fieldType' => 'show', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '可用佣金', 'fieldValue' => 'commision', 'fieldType' => 'show', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '累计佣金', 'fieldValue' => 'total_commision', 'fieldType' => 'show', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
        );
		return $FormData[$index];
	}

}
?>