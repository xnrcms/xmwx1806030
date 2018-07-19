<?php
namespace Admin\Controller;
/**
 * 后台分类管理控制器
 */
class AreaController extends AdminController {
	/**
	 * 分类管理列表
	 */
	public function index(){
		$pid  		= I('get.pid',0);
		$title      = trim(I('get.title'));
		if($pid){
			$data = M('Area')->where("id={$pid}")->field(true)->find();
			$this->assign('data',$data);
		}
		$map['pid'] =   $pid;
		if($title)
		{
			$map['area']  = array('like', '%'.(string)$title.'%');
		}
		C('LIST_ROWS',100);
		$list = $this->lists('Area',$map,'id desc');
		$this->assign('list', $list);
		$this->NavTitle = '地区管理';
		$this->display();
	}
	/* 编辑分类 */
	public function edit($id = null, $pid = 0){
		$Area = D('Area');
		if(IS_POST){ //提交表单
			if(false !== $Area->update()){
				$this->success('编辑成功！', U('index'));
			} else {
				$error = $Area->getError();
				$this->error(empty($error) ? '未知错误！' : $error);
			}
		} else {
			$map["pid"]=0;
			$list=M("area")->where($map)->select();
			$this->assign('list', $list);
			/* 获取分类信息 */
			$info = $id ? $Area->info($id) : '';

			$this->assign('info',       $info);
			$this->assign('Area',   $cate);
			$this->NavTitle = '编辑地区';
			$this->display();
		}
	}
	/* 新增分类 */
	public function add($pid = 0){
		$Area = D('Area');

		if(IS_POST){ //提交表单
			if(false !== $Area->update()){
				$this->success('新增成功！', U('index'));
			} else {
				$error = $Area->getError();
				$this->error(empty($error) ? '未知错误！' : $error);
			}
		} else {
			$map["pid"]=0;
			$list=M("area")->where($map)->select();
			$this->assign('list', $list);

			/* 获取分类信息 */
			$this->assign('info',       null);
			$this->assign('Area', $cate);
			$this->NavTitle = '新增地区';
			$this->display('edit');
		}
	}
	public function change($pid){
	 $pid=I('post.pid',0,'intval'); // 用intval过滤$_POST['pid']
	 $pid=safe_replace($pid);//过滤
	 $map["pid"]=$pid;
	 if($data=M("area")->where($map)->select()){
	 	$this->ajaxReturn($data);
	 }

	}
	/* 删除一个分类*/
	public function del(){
		$cate_id = I('id');
		if(empty($cate_id)){
			$this->error('参数错误!');
		}
		//删除该分类信息
		$res = M('Area')->delete($cate_id);
		if($res !== false){
			//记录行为
			action_log('Area', $cate_id, UID);
			$this->success('删除成功！');
		}else{
			$this->error('删除失败！');
		}
	}
}
?>