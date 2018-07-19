<?php
namespace Admin\Controller;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
/**
 * 文件控制器
 * 主要用于下载模型的文件上传和下载
 */
class FileController extends AdminController {
	/* 文件上传 */
	public function upload(){
		$return  		= array('status' => 1, 'info' => '上传成功', 'data' => '');
		/* 调用文件上传组件上传文件 */
		$File 			= D('File');
		$file_driver 	= C('DOWNLOAD_UPLOAD_DRIVER');
		$info 			= $File->upload($_FILES,C('DOWNLOAD_UPLOAD'),C('DOWNLOAD_UPLOAD_DRIVER'),C("UPLOAD_{$file_driver}_CONFIG"));

		/* 记录附件信息 */
		if($info){
			$return['data'] = think_encrypt(json_encode($info['download']));
			$return['info'] = $info['download']['name'];
		} else {
			$return['status'] = 0;
			$return['info']   = $File->getError();
		}
		/* 返回JSON数据 */
		$this->ajaxReturn($return);
	}

	/* 下载文件 */
	public function download($id = null){
		if(empty($id) || !is_numeric($id)){
			$this->error('参数错误！');
		}
		$logic 			= D('Download', 'Logic');
		if(!$logic->download($id)){
			$this->error($logic->getError());
		}
	}
	/**
	 * 上传图片
	 */
	public function uploadPicture(){
		//TODO: 用户登录检测
		/* 返回标准数据 */
		$return  			= array('status' => 1, 'info' => '上传成功', 'data' => '');
		/* 调用文件上传组件上传文件 */
		$Picture 			= D('Picture');
		$pic_driver 		= C('PICTURE_UPLOAD_DRIVER');
		$pic_driver_conf 	= C("UPLOAD_{$pic_driver}_CONFIG");
		//扩展上传多个缩略图
		$addConf			= array();
		$sizeArray			= array();
		if (strtolower($pic_driver) === 'local'){
			$uploadsize		= I('post.uploadsize');
			if (!empty($uploadsize)){
				$sizeArr	= explode(',', $uploadsize);
				if (!empty($sizeArr)){
					foreach ($sizeArr as $v){
						$sz	= explode('*', $v);
						if ($sz[0]>0 && $sz[1] >0){
							$sizeArray[]	= array('width'=>$sz[0],'height'=>$sz[1]);
						}
					}
				}
			}
			$isThumb		= !empty($sizeArray) ? true : false;
			$addConf		= array('thumbSize'=>$sizeArray,'isThumb'=>$isThumb);
		}
		$pic_driver_conf	= array_merge($pic_driver_conf,$addConf);
		$info 				= $Picture->upload($_FILES,C('PICTURE_UPLOAD'),C('PICTURE_UPLOAD_DRIVER'),$pic_driver_conf); //TODO:上传到远程服务器
		
		$info['download']['path']   = $info['download']['url'];
		M('picture')->where(array('id'=>$info['download']['id']))->setField(array('path'=>$info['download']['path']));
		/* 记录图片信息 */
		if($info){
			$return['status'] = 1;
			$return = array_merge($info['download'], $return);
		} else {
			$return['status'] = 0;
			$return['info']   = $Picture->getError();
		}
		/* 返回JSON数据 */
		$this->ajaxReturn($return);
	}
}
?>