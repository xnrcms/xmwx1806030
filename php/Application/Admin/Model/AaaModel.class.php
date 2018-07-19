<?php
namespace Admin\Model;
use Think\Model;
/**
 * 模型
 */
class AaaModel extends Model{
	/*
	 * ===========自动验证定义说明==============
	 * Field		必填-需要验证的表单字段名称，这个字段不一定是数据库字段，也可以是表单的一些辅助字段，例如确认密码和验证码等等。
	 * 					  有个别验证规则和字段无关的情况下，验证字段是可以随意设置的，例如expire有效期规则是和表单字段无关的。如果定义了字段映射的话，这里的验证字段名称应该是实际的数据表字段而不是表单字段
	 * Rule			必填-要进行验证的规则，需要结合附加规则，如果在使用正则验证的附加规则情况下，系统还内置了一些常用正则验证的规则，可以直接作为验证规则使用，
	 * 					  包括：require 字段必须、email 邮箱、url URL地址、currency 货币、number 数字。
	 * ErrorMsg		必填-用于验证失败后的提示信息定义
	 * CheckTime	不必-验证条件
	 * 				self::EXISTS_VALIDATE 或者0	:存在字段就验证(默认)
	 * 				self::MUST_VALIDATE   或者1 	:必须验证
	 * 				self::VALUE_VALIDATE  或者2 	:值不为空的时候验证 
	 * Rule2		不必-配合验证规则使用，包括下面一些规则
	 * 				regex		:正则验证，定义的验证规则是一个正则表达式(默认)
	 * 				function	:函数验证，定义的验证规则是一个函数名
	 * 				callback	:回调方法验证，定义的验证规则是当前模型类的一个方法
	 * 				confirm 	:验证表单中的两个字段是否相同，定义的验证规则是一个字段名
	 * 				equal 		:验证是否等于某个值，该值由前面的验证规则定义
	 * 				notequal 	:验证是否不等于某个值，该值由前面的验证规则定义（3.1.2版本新增）
	 * 				in 			:验证是否在某个范围内，定义的验证规则可以是一个数组或者逗号分割的字符串
	 * 				notin 		:验证是否不在某个范围内，定义的验证规则可以是一个数组或者逗号分割的字符串（3.1.2版本新增）
	 * 				length 		:验证长度，定义的验证规则可以是一个数字（表示固定长度）或者数字范围（例如3,12 表示长度从3到12的范围）
	 * 				between 	:验证范围，定义的验证规则表示范围，可以使用字符串或者数组，例如1,31或者array(1,31)
	 * 				notbetween 	:验证不在某个范围，定义的验证规则表示范围，可以使用字符串或者数组（3.1.2版本新增）
	 * 				unique 		:验证是否唯一，系统会根据字段目前的值查询数据库来判断是否存在相同的值，当表单数据中包含主键字段时unique不可用于判断主键字段本身
	 * DoneTime		不必-完成时间
	 * 				self::MODEL_INSERT或者1	:新增数据的时候验证(默认)
	 * 				self::MODEL_UPDATE或者2	:更新数据的时候验证 
	 * 				self::MODEL_BOTH或者3	:全部情况下验证(默认)
	 * */
	protected $_validate = array(
	//array('Field','Rule','ErrorMsg',CheckTime,Rule2,DoneTime),
	);
	/*
	 * ===========自动完成定义说明==============
	 * Field		必填-需要操作的字段
	 * Rule			必填-需要处理的规则，配合附加规则完成
	 * DoneTime		不必-完成时间
	 * 				self::MODEL_INSERT 或者1	:新增数据的时候处理(默认)
	 * 				self::MODEL_UPDATE 或者2	:更新数据的时候处理
	 * 				self::MODEL_BOTH   或者3	:所有情况都进行处理
	 * Rule2		不必-附加规则
	 * 				function:使用函数，表示填充的内容是一个函数名
	 * 				callback:回调方法 ，表示填充的内容是一个当前模型的方法
	 * 				field	:用其它字段填充，表示填充的内容是一个其他字段的值
	 * 				string	:字符串（默认方式）
	 * 				ignore	:为空则忽略（3.1.2新增）
	 * */
	protected $_auto = array(
	//array('Field',Rule,DoneTime,Rule2)
	array('create_time', NOW_TIME, self::MODEL_INSERT),
	array('update_time', NOW_TIME, self::MODEL_BOTH),
	array('status', '1', self::MODEL_INSERT, 'string'),
	array('uid', 'is_login', self::MODEL_INSERT, 'function'),
	);
	/**
	 * 获取详细信息
	 */
	public function info($id, $field = true){
		$map['id'] = $id;
		return $this->field($field)->where($map)->find();
	}
	/**
	 * 更新数据
	 */
	public function update($updata = array()){
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
	//============================自定义模型内函数============================
	
	//============================自定义模型内函数============================
}