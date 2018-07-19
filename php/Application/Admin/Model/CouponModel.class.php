<?php
namespace Admin\Model;
use Think\Model;
/**
 * 配置模型
 * @author xiaoQ
 */
class CouponModel extends Model {
	
    protected $_validate = array(
        array('name', 'require', '请输入优惠券名称', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    	array('pic', 'require', '请输入优惠券图片', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    	array('money', 'require', '请输入优惠券面额', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    	array('money', 'currency', '优惠券面额格式不正确', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    	//array('minmoney', 'require', '请输入最低消费额', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    	//array('minmoney', 'currency', '最低消费额格式不正确', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    	array('endtime', 'require', '请输入有效期截止时间', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    	array('grantnum', 'require', '请输入发放张数', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    	array('grantnum', 'number', '张数必须为整数', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    protected $_auto = array(
    	array('name', 'trim', self::MODEL_BOTH, 'function'),
    	array('endtime', 'strtotime', self::MODEL_BOTH, 'function'),
        array('starttime', NOW_TIME, self::MODEL_INSERT),
    );
    
	/**
	 * 更新数据
	 */
	public function update($updata = array())
	{
		$data = $this->create($updata);
		if(!$data) return false;
		if(floatval($data['money'])<=0){
			$this->error = '优惠券面额必须大于零';
			return false;
		}
		if(floatval($data['minmoney'])>0){
			if($data['minmoney']<=$data['money']){
				$this->error = '优惠券面额不能大于最低消费额';
				return false;
			}
			
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