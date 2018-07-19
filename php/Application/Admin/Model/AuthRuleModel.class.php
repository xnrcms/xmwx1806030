<?php
namespace Admin\Model;
use Think\Model;
/**
 * 权限规则模型
 */
class AuthRuleModel extends Model{
    const RULE_URL = 1;
    const RULE_MAIN = 2;

	/**
	 * 更新数据
	 */
	public function update($updata = array())
	{
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
}