<?php
namespace Admin\Model;
use Think\Model;
/**
 * 配置模型
 * @author xiaoQ
 */
class IntervalModel extends Model {
	
    protected $_validate = array(
        array('lower', 'require', '请输入起始数目', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    	array('lower', 'number', '起始数目必须为整数', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    	array('higher', 'require', '请输入截止数目', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    	array('higher', 'number', '截止数目必须为正整数', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    protected $_auto = array(
    );
    
	/**
	 * 更新数据
	 */
	public function update($updata = array())
	{
		$data = $this->create($updata);
		if(!$data) return false;
		if($data['type']<=0){
			$this->error = '请选择类型！';return false;
		}
		if($data['lower']>$data['higher']){
			$this->error = '起始数目不能大于截止数目！';return false;
		}
		/* 添加或更新数据 */
		if(empty($data['id'])){
			$res = $this->add();
			if(!$res) {$this->error = '新增失败！';return false;}
			$data['ac']	= 0;//添加
			$data['id']	= $res;
		}else{
			$res = $this->save();
			if(false === $res) {$this->error = '更新失败！';return false;}
			$data['ac']	= 1;//编辑
			$data['id']	= $data['id'];
		}
		return $data;
	}

}