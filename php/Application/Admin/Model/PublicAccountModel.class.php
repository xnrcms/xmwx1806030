<?php
namespace Admin\Model;
use Think\Model;
/**
 * 公众账号管理模型
 * 2016-03-25
 */
class PublicAccountModel extends Model{
	protected $_validate = array(
	);
	protected $_auto = array(
	);
	/**
	 * 获取公众账号管理详细信息
	 */
	public function info($id, $field = true)
	{
		/* 获取公众账号管理信息 */
		$map['id'] = $id;
		return $this->field($field)->where($map)->find();
	}
	/**
	 * 更新公众账号管理信息
	 */
	public function update($model_id,$updata = array())
	{
		$this->checkModelAttr($model_id);
		$data = $this->create($updata);
		if(!$data) return false;
		/* 添加或更新数据 */
		if(empty($data['id'])){
			$res = $this->add();
			if(!$res) {$this->error = '新增失败！';return false;}
		}else{
			$res = $this->save();
			if(false === $res) {$this->error = '更新失败！';return false;}
		}
		$data['ac']	= $data['id'] >0 ? 1 : 0;//添加还是编辑
		$data['id']	= $data['id'] >0 ? $data['id'] : $res;
		return $data;
	}
	/**
	 * 检测属性的自动验证和自动完成属性
	 * @return boolean
	 */
	public function checkModelAttr($model_id){
		if ($model_id <= 0) return true;
		$fields     =   get_model_attribute($model_id,false);
		$validate   =   $auto   =   array();
		foreach($fields as $key=>$attr)
		{
			if($attr['is_must']){//必填字段
				$validate[]  =  array($attr['name'],'require',$attr['title'].'不能为空!',self::MUST_VALIDATE , 'regex', self::MODEL_BOTH);
			}
			// 自动验证规则
			$validate_list	= M('AttributeCheck')->where(array('field_id'=>$attr['id']))->select();
			if (!empty($validate_list))
			{
				foreach ($validate_list as $ck=>$cv)
				{
					if(!empty($cv['validate_rule'])) {
						$validate[]  =  array($attr['name'],$cv['validate_rule'],$cv['error_info']?$cv['error_info']:$cv['title'].'验证错误',0,$cv['validate_type'],$cv['validate_time']);
					}
				}
			}
			// 自动完成规则
			if(!empty($attr['auto_rule'])) {
				$auto[]  =  array($attr['name'],$attr['auto_rule'],$attr['auto_time'],$attr['auto_type']);
			}elseif('checkbox'==$attr['type']){ // 多选型
				$auto[] =   array($attr['name'],'arr2str',3,'function');
			}elseif('datetime' == $attr['type']){ // 日期型
				$auto[] =   array($attr['name'],'strtotime',3,'function');
			}
		}
		$validate   =   array_merge($validate,$this->_validate);
		$auto       =   array_merge($auto,$this->_auto);
		return $this->validate($validate)->auto($auto);
	}
	protected function GetApiToken()
	{
		return randomString(32);
	}
}