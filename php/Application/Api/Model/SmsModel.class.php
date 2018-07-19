<?php
namespace Api\Model;
use Think\Model;
/**
 * 短信记录模型
 * @author 王远庆
 */
class SmsModel extends Model{
    protected $_validate = array(
		array('mobile', 'IsMobile', '手机格式不正确', self::EXISTS_VALIDATE, 'callback'), //手机格式不正确 TODO:
        array('content', 'require', '短信内容不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
    );
  	protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
        array('status', 1, self::MODEL_INSERT),
        array('ip', 'getip', self::MODEL_INSERT,'function'),
    );
    /**
     * 获取短信记录详细信息
     * @param  milit   $id 短信记录ID或标识
     * @param  boolean $field 查询字段
     * @return array     短信记录信息
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function info($id, $field = true){
        /* 获取短信记录信息 */
        $map['id'] = $id;
		return $this->field($field)->where($map)->find();
    }
    /**
     * 更新短信记录信息
     * @return boolean 更新状态
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
	public function update($updata = array())
	{
		$data = $this->create($updata);
		if(!$data) return false;
		/* 添加或更新数据 */
		if(empty($data['id'])){
			$uid = $this->add();
			if(!$uid){
				$this->error = '新增失败！';
				return false;
			}
		}else{
			$res = $this->save();
			if(false === $res){
				$this->error = '更新失败！';
				return false;
			}
		}
		$data['id']	= $data['id'] >0 ? $data['id'] : $uid;
		return $data;
	}
	
	/*
	 * 手机号是否合法
	 */
	protected function IsMobile()
	{
		$mobile		= I('post.mobile');
		return Mobile_check($mobile,array(1)) ? true : false;
	}
}
