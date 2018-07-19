<?php
namespace Api\Controller;
/**
 * 文件控制器
 * 主要用于下载模型的文件上传和下载
 */
class UploadController extends CommonController {
	
	/**
	 * 上传图片
	 */
	public function uploadPicture(){
		
		$CheckParam	= array(
				array('time','Int',1,$this->Lang['100001'],'100001'),
				array('hash','String',1,$this->Lang['100002'],'100002'),
				array('uploadname','String',1,$this->Lang['100301'],'100301')
		);
		$BackData 				= $this->CheckData(I('request.'),$CheckParam);
		/* 调用文件上传组件上传文件 */
		$Picture 		= D('Picture');
		$pic_driver 	= C('PICTURE_UPLOAD_DRIVER');
		//TODO:上传到远程服务器
		$info 			= $Picture->upload($_FILES,C('PICTURE_UPLOAD'),C('PICTURE_UPLOAD_DRIVER'),C("UPLOAD_".$pic_driver."_CONFIG"));
		
		/* 记录图片信息 */
		if($info[$BackData['uploadname']]){
			$this->ReturnJson(array('Code' =>'0','Msg'=>'成功','Data'=>$info[$BackData['uploadname']]['url']));
		} else {
			$this->ReturnJson(array('Code' =>'1','Msg'=>$Picture->getError()));
		}
	}
	
	
	/**
	 * 上传图片
	 */
	public function picture(){
		$CheckParam	= array(
		array('time','Int',1,'服务器时间异常','1'),
		array('hash','String',1,'签名错误','2'),
		array('uploadname','String',1,'上传名称不能为空','1001')
		);
		$BackData 		= $this->CheckData(I('request.'),$CheckParam);
		/* 调用文件上传组件上传文件 */
		$Picture 		= D('Picture');
		$pic_driver 	= C('PICTURE_UPLOAD_DRIVER');
		//TODO:上传到远程服务器
		$info 			= $Picture->upload($_FILES,C('PICTURE_UPLOAD'),C('PICTURE_UPLOAD_DRIVER'),C("UPLOAD_".$pic_driver."_CONFIG"));
		/* 记录图片信息 */
		if($info[$BackData['uploadname']]){
			$imgid		= $info[$BackData['uploadname']]['id'];
			$this->ReturnJson(array('code' =>'0','msg'=>'ok','data'=>$imgid));
		} else {
			$this->ReturnJson(array('code' =>'1002','msg'=>"系统提示:".$Picture->getError()));
		}
	}
	//WEB上传
	public function pictureWeb(){
		$CheckParam	= array(
		array('time','Int',1,'服务器时间异常','1'),
		array('hash','String',1,'签名错误','2'),
		array('uploadname','String',1,'上传名称不能为空','1001'),
		);
		$BackData 		= $this->CheckData(I('request.'),$CheckParam);
		/* 调用文件上传组件上传文件 */
		$Picture 		= D('Picture');
		$pic_driver 	= C('PICTURE_UPLOAD_DRIVER');
		//TODO:上传到远程服务器
		$info 			= $Picture->upload($_FILES,C('PICTURE_UPLOAD'),C('PICTURE_UPLOAD_DRIVER'),C("UPLOAD_{$pic_driver}_CONFIG"));
		/* 记录图片信息 */
		if($info[$BackData['uploadname']]){
			$info[$BackData['uploadname']]['url']	= 'http://'.WEB_DOMAIN.$info[$BackData['uploadname']]['path'];
			$this->ReturnJson(array('code' =>'0','msg'=>'ok','data'=>$info[$BackData['uploadname']]));
		} else {
			$this->ReturnJson(array('code' =>'1002','msg'=>"系统提示:".$Picture->getError()));
		}
	}
}
