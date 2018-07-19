<?php
namespace Admin\Controller;
/**
 * 后台控制器
 */
class WithdrawController extends AdminController {
	/**
	 * @author xiaoQ
	 */
	public function index(){
		$export = I('get.export',0,'intval');
		if($export == 1){
    		$limit = 1000;
    	}else{
    		$limit = 20;
    	}

		//获取数据
		$MainTab					= 'Withdraw';
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
		$RelationTab				= array(
		//'member'=>array('Ralias'=>'me','Ron'=>'me ON me.uid=main.uid','Rfield'=>array('uid as uuid','nickname')),
		);
		$RelationTab				= $this->getRelationTab($RelationTab);
		$tables	  					= $RelationTab['tables'];
		$RelationFields				= $RelationTab['fields'];
		$model						= !empty($tables) ? $MainModel->join ( $tables ,'LEFT') : $MainModel;

		//检索条件
		$map 						= array();
		if(!IS_ROOT && $this->group_id != 1){
			$map['uid'] = UID;
		}
		$cate_id					= intval(I('get.cate_id',0));
		$cid						= array();
		if ($cate_id >0){
			$cid 					= D('Category')->getChildrenId($cate_id);
			$map['category_id']		= array(array('in',$cid),array('gt',0));
		}

		//时间区间检索
		$search_time				= time_between('create_time',$MainAlias);
		//关键词检索
		$keyword 					= I('find_keyword','');
		if(!empty($keyword)){
			$map['_complex'] 		= array(
				'realName' => array('like', '%'.$keyword.'%'),
				'_logic' 	=> 'OR',
			);
		}

		//状态检索
		$status 				= I('get.find_status',0);
		if($status != ''){
			$map['status'] 		= $status;
		}

		$map						= array_merge($map,$search_time);
		//排序
		$order						= $MainAlias.'.id desc';

		//检索字段
		$fields						= (empty($MainField) ? $this->get_fields_string($MainModel->getDbFields(),$MainAlias).',' : $this->get_fields_string($MainField,$MainAlias).',') . $RelationFields;
		$fields						= trim($fields,',');

		//列表数据
		$status						= array('待审核','审核通过','审核未通过');
		$list 						= $this->getLists($model,$map,$order,$fields,1,$limit,true);
		
		if (!empty($list)){
			foreach ($list as $k=>$v){
				//商家名称
				$list[$k]['shop_name']				= M('shop')->where(array('uid'=>$v['uid']))->getField('shop_name');
				//数据格式化
				$list[$k]['status_name']			= $status[$v['status']];
				//银行卡信息
				$bankCardInfo 						= M('bank_card')->where(array('id'=>$v['card_id']))->find();
				$list[$k]['user_name']				= $bankCardInfo['user_name'];
				$list[$k]['bank_name']				= $bankCardInfo['bank_name'];
				$list[$k]['card_number']			= $bankCardInfo['card_number'];
			}
			//******导出excel数据整理start******
			
			if($export == 1){
				$info = array();
				foreach($list as $k=>$v){
					$info[$k]['id']			= $v['id'];
				}
				$dataResult = $info;
				$title = "提现列表";
				$headtitle = "";
				$titlename = "<tr style='text-align: center;'><th style='width:50px;'>id</th><th style='width:110px;'>姓名</th><th style='width:180px;'>银行卡号</th><th style='width:180px;'>开户行</th><th style='width:100px;'>提现金额</th><th style='width:150px;'>申请时间</th><th style='width:180px;'>审核状态</th><th style='width:180px;'>操作人</th></tr>";
				$filename = $title.".xls";
				R('Admin/Order/excelData', array($dataResult, $titlename, $headtitle, $filename));
			}
			//******导出excel数据整理end******
		}
		$this->assign('_list', $list);
		//操作菜单,可以根据需要固定$menuid,$menuid为Menu表中的ID
		$menuid								= $this->menuid;
		$SonMenu							= $this->getSonMenu($menuid);
		$this->assign('ListTopNav', 		!empty($SonMenu['TOPMENU']) ? $SonMenu['TOPMENU'] : array());
		$this->assign('ListRightNav', 		!empty($SonMenu['RIGHTMENU']) ? $SonMenu['RIGHTMENU'] : array());

		//代码扩展
		$this->extends_param				.= $this->extends_param .'&cate_id='.I('get.cate_id'); 
		//.........
		//代码扩展

		$this->NavTitle = '提现管理';
		$ParentCatName						= D('Category')->getParentName(3,1);
		if (empty($ParentCatName)){
			$this->assign('SmallNav', 			array('提现管理','提现列表'));
		}else{
			$cname[]	= '提现管理';
			foreach ($ParentCatName as $v){
				$cname[]	= $v['name'];
			}
			$this->assign('SmallNav', 			$cname);
		}
		//记录当前列表页的cookie
		if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf')) Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->display();
	}
	
	/**
	 * 审核
	 */
	public function check($id = 0){
		//数据提交
		if (IS_POST) $this->update();
		if($id<=0){
			$this->error('参数错误');
		}
		//页面数据
		$info 			= M('withdraw')->field(true)->find($id);
		if(false === $info){
			$this->error('获取信息错误');
		}
		//提现商家
		$info['shop_name'] 		= M('shop')->where(array('uid'=>$info['uid']))->getField('shop_name');
		//银行卡信息
		$bankCardInfo			= M('bankCard')->where(array('id'=>$info['card_id']))->find();
		$info['user_name'] 		= $bankCardInfo['user_name'];
		$info['bank_name'] 		= $bankCardInfo['bank_name'];
		$info['card_number'] 	= $bankCardInfo['card_number'];
		$info['create_time'] 	= date('Y-m-d H:i:s',$info['create_time']);
		$this->NavTitle = '审核';
		$this->assign('info',$info);
	
		//表单数据
		$FormData						= $this->CustomerForm(0);
		$this->assign('FormData',       $FormData);
		
		$this->display();
	}

	/**
	 * 删除数据
	 */
	public function del(){
		$ids			= I('request.ids');
		if ( empty($ids) ) { $this->error('请选择要操作的数据!');}
		$ids 			= is_array($ids) ? $ids : array(intval($ids));
		$ids			= array_unique($ids);
		$map 			= array('cashAuditId' => array('in', $ids) );
		if(D('Withdraw')->where($map)->delete()){
			//记录行为
			action_log('config',$ids,UID);
			//数据返回
			$this->success('删除成功',Cookie('__forward__'));
		} else {
			$this->error('删除失败！');
		}
	}

	//提交表单
	protected function update(){
		if(IS_POST){
			$Models 		= D('Withdraw');
			//数据整理
			//.......
			//数据整理
			$res 			= $Models->update();
			if(false !== $res){
				//记录行为
				//action_log('article',$data['id'],UID);
				//数据返回
				$this->success('审核成功', Cookie('__forward__'));
			}
			else
			{
				$error = $Models->getError();
				$this->error(empty($error) ? '未知错误！' : $error);
			}
		}
		$this->error('非法提交！');
	}
	
	//是否打款
	public function pay(){
		$id = array_unique((array)I('request.ids'));
		$map = array();
		$map['id'] 	= array('in',$id);
		if( M('Withdraw')->where($map)->save(array('pay_status'=>1))!==false ) {
			$this->success('操作成功', Cookie('__forward__'));
		}else{
			$this->error('操作成功');
		}
	}
	
	/**
	 * 提现
	 */
	public function getWithdraw(){
		//操作权限
		if(!IS_ROOT && $this->group_id != 1){
			$this->error('只有平台才可以打款');
		}
		
		if (IS_POST){
			$uid 			= I('post.uid', 0, 'intval');
			$cardId 		= I('post.card_id', 0, 'intval');
			$money 			= I('post.money', '', 'trim');
			
			if($uid <= 0){
				$this->error('请选择打款用户');
			}
			if($cardId <= 0){
				$this->error('请选择银行卡');
			}
			if($money == ''){
				$this->error('请输入提现金额');
			}
			$money = floatval($money);
			if($money <= 0){
				$this->error('提现金额不能小于等于0');
			}
			$account = M('member')->where(array('uid'=>$uid))->getField('account');
			if($account < $money){
				$this->error('提现金额不能大于账户余额');
			}
			//开启事务
			M()->startTrans();
			//总店钱包减去提现的金额
			$res1 = D('member')->where(array('uid'=>$uid, 'account'=>array('egt',$money)))->setDec('account', $money);
			//总店提现表入库
			$data = array();
			$data['uid'] 			= $uid;
			$data['card_id'] 		= $cardId;
			$data['money'] 			= $money;
			$data['create_time'] 	= NOW_TIME;
			$res2 = M('play_money_record')->add($data);
			if($res1 !== false && $res2 !== false){
				//事务提交
				M()->commit();
				$this->success('提交成功！', Cookie('__forward__'));
			}else{
				M()->rollback();	//事务回滚
				$this->error('提现操作有误');
			}
		}else{
			$info 	= array();
			$uid 	= I('get.id', 0, 'intval');
			if($uid <= 0){
				$this->error('提现用户参数有误');
			}
			$info['account'] 	= M('member')->where(array('uid'=>$uid))->getField('account');
			$info['uid'] 		= $uid;
			//银行卡信息
			$bankCardInfo 		= M('bank_card')->where(array('uid'=>$uid))->field(array('id','bank_name','card_number','user_name'))->select();
			$pidData			= array();
			if (!empty($bankCardInfo)){
				foreach ($bankCardInfo as $k=>$v){
					$pidData[$k]	= array('id'=>$v['id'],'name'=>$v['bank_name'].'('.$v['card_number'].' '.$v['user_name'].')');
				}
			}
			$pidData 			= array_merge(array(0=>array('id'=>0,'name'=>'请选择银行卡')), $pidData);
			$info['pidData'] 		= $pidData;
			$this->assign('info',$info);
			
			$FormData = $this->CustomerForm(1,$pidData);
			$this->assign('FormData', $FormData);
			$this->assign('SmallNav', 			array('商家提现','商家提现信息'));
			$this->NavTitle = '商家提现';
			Cookie('__forward__',$_SERVER['REQUEST_URI']);
			$this->display();
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
	protected function CustomerForm($index=0,$pidData=''){
		
		$FormData[0] = array(
			array('fieldName'=>'提现商家:','fieldValue'=>'shop_name','fieldType'=>'show','isMust'=>0,'fieldData'=>array(),'attrExtend'=>''),
			array('fieldName'=>'银行卡号:','fieldValue'=>'card_number','fieldType'=>'show','isMust'=>0,'fieldData'=>array(),'attrExtend'=>''),
			array('fieldName'=>'开户行:','fieldValue'=>'bank_name','fieldType'=>'show','isMust'=>0,'fieldData'=>array(),'attrExtend'=>''),
			array('fieldName'=>'鑫豆:','fieldValue'=>'xindou','fieldType'=>'show','isMust'=>0,'fieldData'=>array(),'attrExtend'=>''),
			array('fieldName'=>'升值比例:','fieldValue'=>'appreciation','fieldType'=>'show','isMust'=>0,'fieldData'=>array(),'attrExtend'=>''),
			array('fieldName'=>'提现金额:','fieldValue'=>'money','fieldType'=>'show','isMust'=>0,'fieldData'=>array(),'attrExtend'=>''),
			array('fieldName'=>'申请时间:','fieldValue'=>'create_time','fieldType'=>'show','isMust'=>0,'fieldData'=>array(),'attrExtend'=>''),
			array('fieldName'=>'审核:','fieldValue'=>'status','fieldType'=>'radio','isMust'=>1,'fieldData'=>array(1=>'审核通过',2=>'审核未通过'),'attrExtend'=>'placeholder=""'),
			array('fieldName'=>'隐藏域','fieldValue'=>array('id'),'fieldType'=>'hidden','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder=""'),
		);
		
		$FormData[1] = array(
				array('fieldName'=>'可提现金额','fieldValue'=>'account','fieldType'=>'show','isMust'=>0,'fieldData'=>array(),'attrExtend'=>''),
				array('fieldName'=>'选择银行卡','fieldValue'=>'card_id','fieldType'=>'select','isMust'=>1,'fieldData'=>$pidData,'attrExtend'=>'placeholder=""'),
				array('fieldName'=>'提现金额','fieldValue'=>'money','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入提现金额"'),
				array('fieldName'=>'隐藏域','fieldValue'=>array('uid'),'fieldType'=>'hidden','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder=""'),
		);
		return $FormData[$index];
	}
}
?>