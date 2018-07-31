<?php
namespace Admin\Controller;
/**
 * 后台控制器
 */
class PlatformController extends AdminController {
	/**
	 * @author xiaoQ
	 */
	public function index(){
		
		//$this->display();
	}
	
	/**
	 * 设置
	 */
	public function config(){
		//操作权限
		if (IS_POST){
			$param = I('post.');
			if($param['MOBILE'] == ''){
				$this->error('请输入平台电话');
			}
			/* if($param['SHARE_TITLE'] == ''){
				$this->error('请输入分享标题');
			}
			if($param['SHARE_CONTENT'] == ''){
				$this->error('请输入分享内容');
			}
			if($param['SHARE_IMG'] == ''){
				$this->error('请输入分享图片');
			} */
			foreach ($param as $key=>$value){
				M('platformConfig')->where(array('name'=>$key))->save(array('value'=>$value));
			}
			$this->success('提交成功！', Cookie('__forward__'));
		}else{
			$platformConfig = M('platformConfig')->select();
			$info 	= array();
			foreach ($platformConfig as $key=>$value){
				$info[$value['name']] 	= $value['value'];
			}
			$this->assign('info',$info);
			$FormData 	= $this->CustomerForm(0);
			$this->assign('FormData', $FormData);
			$this->assign('SmallNav', 			array('平台信息','平台设置'));
			$this->NavTitle = '平台设置';
			Cookie('__forward__',$_SERVER['REQUEST_URI']);
			$this->display();
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
			array('fieldName'=>'平台电话','fieldValue'=>'MOBILE','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入平台电话"'),
			//array('fieldName'=>'折扣','fieldValue'=>'DISCOUNT','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入折扣"'),
			//array('fieldName'=>'分享标题','fieldValue'=>'SHARE_TITLE','fieldType'=>'text','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入分享标题"'),
			//array('fieldName'=>'分享内容','fieldValue'=>'SHARE_CONTENT','fieldType'=>'textarea','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入分享内容" rows="5" style="height:100%;"'),
			//array('fieldName'=>'分享图片','fieldValue'=>'SHARE_IMG','fieldType'=>'image','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'data-table="demo" data-field="image" data-size=""'),
			//array('fieldName'=>'QQ1','fieldValue'=>'QQ_ONE','fieldType'=>'text','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入QQ1"'),
			//array('fieldName'=>'QQ2','fieldValue'=>'QQ_TWO','fieldType'=>'text','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder="请输入QQ2"'),
		);
		return $FormData[$index];
	}
}
?>