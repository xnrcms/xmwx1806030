<?php
namespace Admin\Controller;
/**
 * 后台配置控制器
 */
class ConfigController extends AdminController {
	/**
	 * 配置管理
	 * @author 王远庆
	 */
	public function index(){
		$limit						= 20;

		//获取数据
		$MainTab					= 'Config';
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
		$map  						= array('main.status' => 1);
		if(isset($_GET['group'])){
			$groupid				= intval(I('group',0));
			$map['main.group']   	= $groupid > 0 ? $groupid : 0;
		}
		if(isset($_GET['name'])){
			$map['main.name']    	= array('like', '%'.(string)I('name').'%');
		}

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
				$list[$k]['groupName']		= M('menu')->where(array('id'=>$v['group']))->getField('title');
			}
		}
		$this->assign('_list', $list);

		//操作菜单,可以根据需要固定$menuid,$menuid为Menu表中的ID
		$menuid								= $this->menuid;
		$SonMenu							= $this->getSonMenu($menuid);
		$this->assign('ListTopNav', 		!empty($SonMenu['TOPMENU']) ? $SonMenu['TOPMENU'] : array());
		$this->assign('ListRightNav', 		!empty($SonMenu['RIGHTMENU']) ? $SonMenu['RIGHTMENU'] : array());

		//代码扩展
		$group		= $this->GetConfigGroup();
		$this->assign('group',$group);
		$this->assign('group_id',I('get.group',0));
		//.........
		//代码扩展

		$this->NavTitle = '配置列表';
		$this->assign('SmallNav', 			array('菜单管理',$this->NavTitle));
		//记录当前列表页的cookie
		if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf')) Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->display();
	}

	/**
	 * 新增配置
	 */
	public function add(){
		//数据提交
		if (IS_POST) $this->update();

		//页面数据
		$info 							= array();
		$this->assign('info',$info);

		//表单数据
		$FormData						= $this->CustomerForm(0);
		$this->assign('FormData',       $FormData);

		$this->NavTitle = '新增配置';
		$this->display('addedit');
	}

	/**
	 * 编辑配置
	 */
	public function edit($id = 0){
		//数据提交
		if (IS_POST) $this->update();

		//页面数据
		$info = M('Config')->field(true)->find($id);
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
	//提交表单
	protected function update(){
		if(IS_POST){
			$Models 		= D('Config');
			//数据整理
			//.......
			//数据整理
			$res 			= $Models->update();
			if(false !== $res){
				S('DB_CONFIG_DATA',null);
				action_log('config',$data['id'],UID);
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
	 * 批量保存配置
	 */
	protected function saveConfig(){
		if(IS_POST){
			$configInfo		= I('post.');
			if($configInfo && is_array($configInfo)){
				$Config 	= M('Config');
				foreach ($configInfo as $name => $value) {
					$map = array('name' => $name);
					$Config->where($map)->setField('value', $value);
				}
				S('DB_CONFIG_DATA',null);
				$this->success('保存成功！',Cookie('__forward__'));
			}
			$this->error('数据异常！');
		}
		$this->error('非法提交！');
	}

	/**
	 * 删除配置
	 */
	public function del(){
		$ids			= I('request.ids');
		if ( empty($ids) ) { $this->error('请选择要操作的数据!');}
		$ids 			= is_array($ids) ? $ids : array(intval($ids));
		$ids			= array_unique($ids);
		$map 			= array('id' => array('in', $ids) );
		if(M('Config')->where($map)->delete()){
			S('DB_CONFIG_DATA',null);
			//记录行为
			action_log('config',$id,UID);
			$this->success('删除成功',Cookie('__forward__'));
		} else {
			$this->error('删除失败！');
		}
	}

	//基本配置
	public function baseConfig(){
		$this->NavTitle 		= '基本配置';
		$this->config();
	}
	public function systemConfig(){
		$this->NavTitle 		= '系统配置';
		$this->config();
	}
	public function alipayConfig(){
		$this->NavTitle 		= '阿里支付配置';
		$this->config();
	}
	public function wxpayConfig(){
		$this->NavTitle 		= '微信支付配置';
		$this->config();
	}
	public function devConfig(){
		$this->NavTitle 		= '开发配置';
		$this->config();
	}
	protected function config() {
		if (IS_POST) $this->saveConfig();
		//表单数据
		$FormData						= $this->CustomerForm(1);
		$this->assign('FormData',       $FormData);
		$this->assign('SmallNav', 			array('配置管理',$this->NavTitle));
		Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->display('config');
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
		if($index == 0){
			$type		= C('CONFIG_TYPE_LIST');
			$group		= $this->GetConfigGroup();
			$FormData[0] = array(
			array('fieldName'=>'配置标识','fieldValue'=>'name','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入配置标识"'),
			array('fieldName'=>'配置名称','fieldValue'=>'title','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入配置名称"'),
			array('fieldName'=>'配置类型','fieldValue'=>'type','fieldType'=>'radio','isMust'=>0,'fieldData'=>$type,'attrExtend'=>'placeholder=""'),
			array('fieldName'=>'配置分组','fieldValue'=>'group','fieldType'=>'select','isMust'=>0,'fieldData'=>$group,'attrExtend'=>'placeholder=""'),
			array('fieldName'=>'排序','fieldValue'=>'sort','fieldType'=>'text','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder=""'),
			array('fieldName'=>'配置值','fieldValue'=>'value','fieldType'=>'textarea','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入配置值" rows="5" style="height:100%;"'),
			array('fieldName'=>'配置项','fieldValue'=>'extra','fieldType'=>'textarea','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder="如果是枚举型 需要配置该项" rows="5" style="height:100%;"'),
			array('fieldName'=>'配置说明','fieldValue'=>'remark','fieldType'=>'textarea','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入配置说明" rows="5" style="height:100%;"'),
			array('fieldName'=>'隐藏域','fieldValue'=>array('id'),'fieldType'=>'hidden','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder=""'),
			);
		}
		if ($index == 1){
			$url			= CONTROLLER_NAME . '/' . ACTION_NAME;
			$configMenu     = M('menu')->where(array('url'=>$url))->field('id,title')->find();
			$id				= empty($configMenu) ? 0 : $configMenu['id'];
			$list   		= M("Config")->where(array('status'=>1,'group'=>$id))->field('id,name,title,extra,value,remark,type')->order('sort')->select();
			$forms			= array();
			$info			= array();
			if(!empty($list)) {
				foreach ($list as $k=>$v){
					$info[$v['name']]	= $v['value'];
					if(in_array($v['type'], array(0,1,2))){
						$forms[]			= array('fieldName'=>$v['title'],'fieldValue'=>$v['name'],'fieldType'=>'text','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder="'.$v['remark'].'"');
					}elseif (in_array($v['type'], array(3))){
						$forms[]			= array('fieldName'=>$v['title'],'fieldValue'=>$v['name'],'fieldType'=>'textarea','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder="'.$v['remark'].'"');
					}elseif (in_array($v['type'], array(4))){
						$fieldData			= parse_config_attr($v['extra']);
						$forms[]			= array('fieldName'=>$v['title'],'fieldValue'=>$v['name'],'fieldType'=>'select','isMust'=>0,'fieldData'=>$fieldData,'attrExtend'=>'placeholder="'.$v['remark'].'"');
					}elseif (in_array($v['type'], array(5))){
						$attrExtend			= 'data-table="config" data-field="'.$v['name'].'" data-size=""';
						$forms[]			= array('fieldName'=>$v['title'],'fieldValue'=>$v['name'],'fieldType'=>'image','isMust'=>0,'fieldData'=>array(),'attrExtend'=>$attrExtend);
					}elseif (in_array($v['type'], array(6))){
						$attrExtend			= 'data-table="config" data-field="'.$v['name'].'" data-size=""';
						$forms[]			= array('fieldName'=>$v['title'],'fieldValue'=>$v['name'],'fieldType'=>'image','isMust'=>0,'fieldData'=>array(),'attrExtend'=>$attrExtend);
					}
				}
			}
			$this->info	 	= $info;
			$FormData[1]	= $forms;
		}
		return $FormData[$index];
	}
	protected function GetConfigGroup(){
		$map['_complex'] 	= array('group' => array('eq', '配置管理'),'title' => array('eq', '开发配置'),'_logic' 	=> 'OR');
		$map['hide']		= 0;
		$map['status']		= 1;
		$group				= M('menu')->where($map)->field('id,title')->order('sort asc')->select();
		$id					= empty($configMenu) ? 0 : $configMenu['id'];
		$this->assign('id',$id);
		if (!empty($group)){
			foreach ($group as $k=>$v){
				$groups[$v['id']]	= $v['title'];
			}
		}
		$group	= array();
		return $groups;
	}
}
?>