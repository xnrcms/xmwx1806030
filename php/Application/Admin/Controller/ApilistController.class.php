<?php
namespace Admin\Controller;
/**
 * 后台接口管理控制器
 */
class ApilistController extends AdminController {
	/**
	 * 接口管理管理
	 */
	public function index(){
		$limit				= 20;
		//获取数据
		$MainTab			= 'apilist';
		$MainAlias			= 'main';
		$MainField			= array();
		//主表模型
		$MainModel 			= M($MainTab)->alias($MainAlias);
		/*
		 * 灵活定义关联查询
		 * Ralias 	关联表别名
		 * Ron    	关联条件
		 * Rfield	关联查询字段，
		 * */
		$RelationTab		= array(
		'member'=>array('Ralias'=>'me','Ron'=>'me ON me.uid=main.uid','Rfield'=>array('uid as uuid','nickname')),
		);
		$RelationTab		= $this->getRelationTab($RelationTab);
		$tables	  			= $RelationTab['tables'];
		$RelationFields		= $RelationTab['fields'];
		$model				= !empty($tables) ? $MainModel->join ( $tables ,'LEFT') : $MainModel;
		//检索条件
		$keyword			= trim(I('get.find_keyword',''));
		$create_time_s		= I('get.create_time_s','');
		$create_time_e		= I('get.create_time_e','');
		if(!empty($keyword)){
			$where['name']  		= array('like', '%'.$keyword.'%');
			$where['apidesc']  		= array('like','%'.$keyword.'%');
			$where['author']  		= array('like','%'.$keyword.'%');
			$where['_logic'] 		= 'or';
			$map['_complex'] 		= $where;
		}
		$create_time				= array();
		if (!empty($create_time_s)){
			$create_time[]	= array('egt',strtotime($create_time_s));
		}
		if (!empty($create_time_e)){
			$create_time[]	= array('elt',strtotime($create_time_e)+86400);
		}
		if (!empty($create_time)){
			$map[$MainAlias.'.create_time']	= array($create_time);
		}

		$map[$MainAlias.'.status']  		= 1;
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
				$list[$k]['create_time']	= date('Y-m-d H:i',$v['create_time']);
				$list[$k]['parames_hash']	= get_fields_hash($v['parames']);
			}
		}
		$this->assign('_list', $list);

		//操作菜单
		$SonMenu							= $this->getSonMenu($this->menuid);
		$this->assign('ListTopNav', 		!empty($SonMenu['TOPMENU']) ? $SonMenu['TOPMENU'] : array());
		$this->assign('ListRightNav', 		!empty($SonMenu['RIGHTMENU']) ? $SonMenu['RIGHTMENU'] : array());

		$this->NavTitle 					= '接口管理';
		$this->assign('SmallNav', 			array('接口管理','接口列表'));
		//记录当前列表页的cookie
		if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf')) Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->display();
	}
	//添加
	public function add()
	{
		if (IS_POST) $this->update();
		$info							= null;
		$FormData						= $this->CustomerForm(0);
		$this->assign('info',       	$info);
		$this->assign('FormData',       $FormData);
		$this->NavTitle 				= '新增接口';
		$this->display('addedit');
	}
	//编辑
	public function edit()
	{
		if (IS_POST) $this->update();
		/* 获取编辑信息 */
		$Models 						= D('apilist');
		$doid							= I('request.id',0,'intval');
		$info 							= $doid >0 ? $Models->info($doid) : null;
		$FormData						= $this->CustomerForm(0);

		$this->assign('info',       	$info);
		$this->assign('FormData',       $FormData);
		$this->NavTitle 				= '编辑接口';
		$this->display('addedit');
	}
	//提交表单
	protected function update()
	{
		if(IS_POST){
			$Models 		= D('apilist');
			$apiname		= I('post.name','');
			if (empty($apiname)) $this->error('接口名称不能为空！');
			$isinit			= I('post.isinit',0);
			if ($isinit == 1){
				$apiurl			= U($apiname,'getapiinfo=1',true,true);
				$backdata 		= CurlHttp($apiurl,$parame,'POST');
				$backdata		= json_decode($backdata,true);
				if ($backdata['Code'] == 0){
					if (!empty($backdata['Data'][1])){
						$fieldinfo			= '';
						$fieldname			= '';
						$fieldcode			= array();
						foreach ($backdata['Data'][1] as $key=>$val)
						{
							if (!in_array($val[0], array('hash'))) $fieldinfo .= $val[0].',';
							if (!in_array($val[0], array('time','hash'))) $fieldname .= $val[3].' '.$val[1].'类型  '.($val[2] == 1 ? '必填' : '不必填').',';
							if($val[2] == 1)	$fieldcode[] = $val[4];
						}
						$_POST['parames']		= trim($fieldinfo,',');
						$_POST['parames_desc']	= trim($fieldname,',');
					}
				}
			}
			$res 			= $Models->update();
			if(false !== $res)
			{
				$this->success($res['ac']>0 ? '更新成功' : '新增成功', Cookie('__forward__'));
			}
			else
			{
				$error = $Models->getError();
				$this->error(empty($error) ? '未知错误！' : $error);
			}
		}
		else
		{
			$this->error('非法提交！');
		}
	}
	//删除
	public function del()
	{
		$Models 		= D('apilist');
		$ids			= I('request.ids');
		if (is_numeric($ids)){
			$ids 		= array($ids);
		}
		if (empty($ids)){
			$this->error('请选择要操作的数据');
		}
		if(is_array($ids)){
			$map['id']	= array('in',$ids);
			$Models->where($map)->delete();
			$this->success("删除成功！", Cookie('__forward__'));
		}
		else
		{
			$this->error('非法提交！');
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
		$FormData[0] = array(
		array('fieldName'=>'接口地址','fieldValue'=>'name','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder=""'),
		array('fieldName'=>'接口名称','fieldValue'=>'apidesc','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder=""'),
		array('fieldName'=>'接口作者','fieldValue'=>'author','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder=""'),
		array('fieldName'=>'参数返回说明','fieldValue'=>'parames_back','fieldType'=>'textarea','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder="" rows="12" style="height:100%;"'),
		array('fieldName'=>'是否初始化','fieldValue'=>'isinit','fieldType'=>'checkbox','isMust'=>0,'fieldData'=>array('1'=>''),'attrExtend'=>'placeholder=""'),
		array('fieldName'=>'隐藏域','fieldValue'=>array('id'),'fieldType'=>'hidden','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder=""'),
		);
		return $FormData[$index];
	}
	//=======================================********扩展功能*********=================================================================================
	//接口调试
	public function debug()
	{
		if (IS_POST && IS_AJAX) $this->start_debug();
		/* 获取编辑信息 */
		$Models 						= D('apilist');
		$doid							= I('request.id',0,'intval');
		$info 							= $doid >0 ? $Models->info($doid) : null;
		$info['url']					= U($info['name'],'',true,true);
		$parames						= !empty($info['parames']) ? explode(',', $info['parames']) : array();
		$parames_desc					= !empty($info['parames_desc']) ? explode(',', $info['parames_desc']) : array();

		if (!empty($parames)){
			foreach ($parames as $k=>$v){
				if ($v == 'id'){
					$parames[$k] = $v.'_xnrcms';
				}elseif ($v == 'time'){
					unset($parames[$k]);
				}
			}
		}
			
		$info['parames']				= $parames;
		$info['parames_desc']			= $parames_desc;
		$info['uid']					= session('api_uid');
		$info['hashid']					= session('api_hashid');
		$this->assign('info',       	$info);
		$this->NavTitle 				= '调试接口';
		$this->display();
	}
	//提交测试
	protected function start_debug()
	{
		/* 获取编辑信息 */
		$Models 						= D('apilist');
		$doid							= I('request.id',0,'intval');
		$info 							= $doid >0 ? $Models->info($doid) : null;
		$apiurl							= U($info['name'],'',true,true);
		$fields							= $info['parames'];
		$fields							= explode(',', $fields);
		$parame['time']					= NOW_TIME;
		$parame['hash']					= get_fields_hash($fields);
		foreach ($fields as $val)
		{
			if ($val != 'time'){
				if ($val == 'updata'){
					$param 	= array();
					$params = $_POST[$val];
					if(!empty($params)){
						$params	= explode(',', $params);
						foreach ($params as $k=>$v){
							$pas 				= explode(':', $v);
							$param[$pas[0]] 	= $pas[1];
						}
					}
					$parame[$val]	= json_encode($param);
				}
				elseif ($val == 'id'){
					$parame[$val]	= $_POST[$val.'_xnrcms'];
				}
				else{
					$parame[$val]	= $_POST[$val];
				}
			}
		}
		$backdata 				= CurlHttp($apiurl,$parame,'POST');
		if (strpos(strtolower($info['name']).'@','login')){
			$logininfo	= json_decode($backdata);
			if ($logininfo->Code == 0){
				session('api_uid',$logininfo->Data->uid);
				session('api_hashid',$logininfo->Data->hashid);
			}
		}
		$this->success($backdata);

	}
	protected function formatt_fields_string($fields)
	{
		$string = '';
		foreach ($fields as $v){
			$string .= $v;
		}
		return $string;
	}
	//=======================================********扩展功能*********=================================================================================
}
?>