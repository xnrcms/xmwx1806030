<?php
namespace Api\Helper;
/**
 * 广告
 * @author 王远庆
 */
class UploadHelper extends BaseHelper{
	//初始化接口
	public function apiRun($parame = ''){
		//接口分发
		$Parame		= !empty($parame) ? json_decode($parame,true) : '';
		$ac			= $Parame['ac'];
		$isapi		= $Parame['isapi'];
		if ($isapi === true){
			return !empty($ac) ? $this->$ac($Parame) : array('Code' =>'100009','Msg'=>$this->Lang['100009']);
		}
		return array('Code' =>'100007','Msg'=>$this->Lang['100007']);
	}

	/**
	 * 头像上传
	 */
	private function face($Parame){
		/* 调用文件上传组件上传文件 */
		$Picture 		= D('Picture');
		$pic_driver 	= C('PICTURE_UPLOAD_DRIVER');
		//TODO:上传到远程服务器
		$info 			= $Picture->upload($_FILES,C('PICTURE_UPLOAD'),C('PICTURE_UPLOAD_DRIVER'),C("UPLOAD_".$pic_driver."_CONFIG"));
		
		/* 记录图片信息 */
		if($info[$Parame['uploadname']]){
			foreach ($info[$Parame['uploadname']] as $k=>$v){
				if ($k == 'path'){
					$info[$Parame['uploadname']][$k] = 'http://'.WEB_DOMAIN.$v;
				}elseif ($k == 'id'){
					$info[$Parame['uploadname']][$k] = $v*1;
				}else{
					unset($info[$Parame['uploadname']][$k]);
				}
			}
			return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$info[$Parame['uploadname']]);
		} else {
			return array('Code' =>'100014','Msg'=>"系统提示:".$Picture->getError());
		}
	}
}
?>