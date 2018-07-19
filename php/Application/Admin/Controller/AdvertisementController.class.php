<?php
namespace Admin\Controller;
/**
 * 后台用户控制器
 */
class AdvertisementController extends AdminController {
	protected $parentid	= 0;
	protected $groupid	= 0;
	/**
	 * 用户列表
	 */
public function index(){
		$limit						= 20;

		//获取数据
		$MainTab					= 'Advertisement';
		$MainAlias					= 'main';
		$MainField 					= array();
		$MainField 					= array();

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
		//时间区间检索
		$search_time				= time_between('create_time',$MainAlias);
		//关键词检索
		$keyword 					= I('find_keyword','');
		if(!empty($keyword)){
			$map['_complex'] 		= array(
				'username' 	=> array('like', '%'.$keyword.'%'),
				'_logic' 	=> 'OR',
			);
		}

		//状态检索
		$status 					= intval(I('get.find_status',0));
		if(!empty($status) && $status > 0){
			$map['status'] 			= $status;
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
				if($v['design_type'] == 1){
					$list[$k]['design_type_name']	= '委托平台设计';
				}elseif($v['design_type'] == 2){
					$list[$k]['design_type_name']	= '自行设计';
				}
				$list[$k]['show_time']				= $v['show_time'] > 0 ? date('Y-m-d',$v['show_time']) : '--';
				$list[$k]['create_time']			= $v['create_time'] > 0 ? date('Y-m-d H:i:s',$v['create_time']) : '--';
			}
		}
		$this->assign('_list', $list);
		//操作菜单,可以根据需要固定$menuid,$menuid为Menu表中的ID
		$menuid								= $this->menuid;
		$SonMenu							= $this->getSonMenu($menuid);
		$this->assign('ListTopNav', 		!empty($SonMenu['TOPMENU']) ? $SonMenu['TOPMENU'] : array());
		$this->assign('ListRightNav', 		!empty($SonMenu['RIGHTMENU']) ? $SonMenu['RIGHTMENU'] : array());

		//代码扩展
		//$this->extends_param				.= $this->extends_param;
		$this->extends_param				= '';
		//.........
		//代码扩展
		
		$this->NavTitle = '平面广告管理';
		$this->assign('SmallNav', 			array('平面广告管理','平面广告列表'));
		//记录当前列表页的cookie
		if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf')) Cookie('__forward__',$_SERVER['REQUEST_URI']);
		
		$this->display();
	}
	
	/**
	 * 数据
	 */
	public function addedit($id = 0){
		//数据提交
		if (IS_POST){
			//id
			$this->update();
		}else{
			if($id){
				//页面数据
				$info 			= M('user')->field(getField(C('CURRENT_LANGUAGE'), M('user')->getDbFields()))->find($id);
				if(false === $info){
					$this->error('获取信息错误');
				}
				$this->NavTitle = '编辑';
				//表单数据
				$FormData						= $this->CustomerForm(0);
			}else{
				//页面数据
				$info 							= array();
				$this->NavTitle = '新增';
				//表单数据
				$FormData						= $this->CustomerForm(0);
			}
			$this->assign('info',$info);
			$this->assign('FormData',       $FormData);
			$this->display();
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
		if(M('advertisement')->where($map)->delete()){
			//记录行为
			action_log('advertisement',$ids,UID);
			//数据返回
			$this->success('删除成功',Cookie('__forward__'));
		} else {
			$this->error('删除失败！');
		}
	}

	//提交表单
	protected function update(){
		if(IS_POST){
			$Models 		= D('User');
			//数据整理
			//.......
			//数据整理
			$res 			= $Models->update();
			if(false !== $res){
				//记录行为
				//action_log('article',$data['id'],UID);
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
	 * 审核
	 */
	/* public function check(){
		$id				= I('request.ids');
		if ( empty($id) ) { $this->error('请选择要操作的数据!');}
		$advertisement = M('advertisement')->where(array('id'=>$id))->find();
		if($advertisement['check_status'] == 2){
			$this->error('已审核,请不要重复审核！');
		}
		$map 			= array('id' => $id );
		if(M('advertisement')->where($map)->save(array('check_status'=>2))){
			//记录行为
			action_log('advertisement',$id,UID);
			//数据返回
			$this->success('审核成功',Cookie('__forward__'));
		} else {
			$this->error('审核失败！');
		}
	} */
	
	/**
	 * 	审核
	 */
	public function check($id = 0) {
		//数据提交
		if (IS_POST){
			$Models = M('advertisement');
			//数据整理
			//.......
			$id 					= I('post.id',0,'intval');
			if($id < 1){
				$this->error('请选择要操作的数据！');
			}
			$check_status 			= I('post.check_status',0,'intval');
			if($check_status < 1){
				$this->error('请选择审核状态！');
			}
			$advertisement 			= M('advertisement')->where(array('id'=>$id))->find();
			if($advertisement['check_status'] != 1){
				$this->error('已审核,请不要重复审核！');
			}
			if($advertisement['design_type'] == 1){
				$design_money 		= I('post.design_money','0','trim');
				$img 				= I('post.img','','trim');
				if($design_money <= 0){
					$this->error('请输入设计费用！');
				}
				if(empty($img)){
					$this->error('请上传设计图片！');
				}
			}
				
			//数据整理
			$row 						= array();
			$row['check_status'] 		= $check_status;
			if($advertisement['design_type'] == 1 && $check_status == 2){
				$row['design_money'] 	= $design_money;
				$row['pay_money'] 		= array('exp',"pay_money+$design_money");
				$row['img'] 			= $img;
			}
			$res = $Models->where(array('id'=>$id))->save($row);
			if($check_status == 3){	//审核不通过释放红包
				M('red_packet')->where(array('order_no'=>$advertisement['order_no']))->save(array('order_no'=>0));
			}
			if (false !== $res) {
				//数据返回
				$this->success('审核成功', Cookie('__forward__'));
			} else {
				$error = $Models->getError();
				$this->error(empty($error) ? '未知错误！' : $error);
			}
		}else{
			//页面数据
			//不同语言字段转换
			$info 	= M('advertisement')->find($id);
	
			if (false === $info) {
				$this->error('获取信息错误');
			}
			$this->assign('info', $info);
	
			//表单数据
			$FormData = $this->CustomerForm(1);
			$this->assign('FormData', $FormData);
	
			$this->NavTitle = '账号审核';
			$this->display('check');
		}
	}
	
	/**
	 * 	红包统计
	 */
	public function packetStatistics($id = 0) {
		
		$id 	= I('get.id',0,'intval');
		
		$order_no = M('advertisement')->where(array('id'=>$id))->getField('order_no');
		
		$redPacket = M('red_packet')->field("count(id) as t, packet_num, order_no")->where(array('order_no'=>$order_no))->group('packet_num')->order('packet_num asc')->select();
		$this->assign('_list', $redPacket);
		
		$this->NavTitle = '红包统计';
		$this->assign('SmallNav', 			array('红包统计','红包统计列表'));
		
		$this->display();
		
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
	protected function CustomerForm($index=0){
		
		//添加
		$FormData[0] = array(
			
		);
		
		//审核
		$FormData[1] = array(
			array('fieldName' => '设计费用', 'fieldValue' => 'design_money', 'fieldType' => 'text', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入设计费用"'),
			array('fieldName' => '设计图片', 'fieldValue'=>'img','fieldType'=>'image','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'data-table="advertisement" data-field="image" data-size=""'),
			array('fieldName' => '审核', 'fieldValue'=>'check_status','fieldType'=>'radio','isMust'=>0,'fieldData'=>array('2'=>'通过','3'=>'不通过'),'attrExtend'=>''),
			array('fieldName' => '隐藏域', 'fieldValue'=>array('id'),'fieldType'=>'hidden','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder=""'),
		);
		return $FormData[$index];
	}

}
?>