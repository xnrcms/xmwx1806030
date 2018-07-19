<?php
namespace Admin\Controller;
/**
 * 后台控制器
 */
class BusinessProfitController extends AdminController {
	/**
	 * @author xiaoQ
	 */
	public function card(){
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
		$map['uid'] 				= BUID;
		$cate_id					= intval(I('get.cate_id',0));

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
		
	}
}
?>