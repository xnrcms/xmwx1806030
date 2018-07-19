<?php
namespace Admin\Controller;
/**
 * 后台配置控制器
 */
class BannerController extends AdminController {
	/**
	 * 修改成自己的
	 * @author xxx
	 */
	public function index(){
		$limit						= 20;

		//获取数据
		$MainTab					= 'banner';
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
		$RelationTab				= $this->getRelationTab($RelationTab);
		$tables	  					= $RelationTab['tables'];
		$RelationFields				= $RelationTab['fields'];
		$model						= !empty($tables) ? $MainModel->join ( $tables ,'LEFT') : $MainModel;

		//检索条件
		$map 						= array();

		//时间区间检索
		//时间区间检索
        $create_time_s=I('create_time_s','');
        $create_time_e=I('create_time_e','');
        if ($create_time_s !== "" && $create_time_e !== "") {
            $create_stime =$create_time_s . ' 00:00:00';
            $create_etime = $create_time_e . ' 23:59:59';
            $create_time_s=strtotime($create_stime);
            $create_time_e=strtotime($create_etime);
            $map['main.create_time'] = array(array('egt', $create_time_s), array('elt', $create_time_e));

        }
		
		
		//状态检索
		$status 				= intval(I('get.find_status',0));
		if(!empty($status) && $status > 0){
			$map['main.status'] 		= $status;
		}

		//排序
		$order						= $MainAlias.'.id desc';

		//检索字段
		$fields						= (empty($MainField) ? $this->get_fields_string($MainModel->getDbFields(),$MainAlias).',' : $this->get_fields_string($MainField,$MainAlias).',') . $RelationFields;
		$fields						= trim($fields,',');

		//列表数据
		$status						= array('禁用','启用');
		$list 						= $this->getLists($model,$map,$order,$fields,1,$limit,true);
		if (!empty($list)){
			foreach ($list as $k=>$v){
				//数据格式化
				if($v['type'] == 1){
					$typeName = '首页轮播';
				}elseif($v['type'] == 3){
					$typeName = '商城轮播';
				}
				$list[$k]['type'] 			= $typeName;
				$list[$k]['status']			= $status[$v['status']];
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
		$info 			= M('banner')->field(true)->find($id);
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
			array('fieldName'=>'分类','fieldValue'=>'type','fieldType'=>'select','isMust'=>1,'fieldData'=>array(1=>'首页轮播', 3=>'商城轮播', 4=>'页面底部广告'),'attrExtend'=>'placeholder=""'),
			array('fieldName'=>'链接','fieldValue'=>'link','fieldType'=>'text','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入链接"'),	
			array('fieldName'=>'显示状态','fieldValue'=>'status','fieldType'=>'radio','isMust'=>1,'fieldData'=>array(1=>'启用',0=>'禁用'),'attrExtend'=>'placeholder=""'),
			array('fieldName'=>'单图上传','fieldValue'=>'picture','fieldType'=>'image','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'data-table="demo" data-field="image" data-size=""'),
			array('fieldName'=>'隐藏域','fieldValue'=>array('id'),'fieldType'=>'hidden','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder=""'),
		);
		return $FormData[$index];
	}
}
?>
