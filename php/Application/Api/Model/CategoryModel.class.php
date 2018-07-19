<?php
namespace Api\Model;
use Think\Model;
/**
 * 分类模型
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class CategoryModel extends Model{

	protected $_validate = array(
	array('name', 'require', '分类名称不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
	);

	protected $_auto = array(
	array('create_time', NOW_TIME, self::MODEL_INSERT),
	array('update_time', NOW_TIME, self::MODEL_BOTH),
	array('status', '1', self::MODEL_INSERT),
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

	/**
	 * 获取分类树，指定分类则返回指定分类极其子分类，不指定则返回所有分类树
	 * @param  integer $id    分类ID
	 * @param  boolean $field 查询字段
	 * @return array          分类树
	 */
	public function getTree($id = 0, $field = true,$map){
		/* 获取当前分类信息 */
		if($id){
			$info = $this->info($id);
			$id   = $info['id'];
		}
		/* 获取所有分类 */
		$list 		= $this->field($field)->where($map)->order('sort desc,id asc')->select();
		$list 		= list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_', $root = $id);
		/* 获取返回数据 */
		if(isset($info)){ //指定分类则返回当前分类极其子分类
			$info['_'] = $list;
		} else { //否则返回所有分类
			$info = $list;
		}
		return $info;
	}

	/**
	 * 获取指定分类子分类信息
	 * @param  string $cate 分类ID
	 * @return string       id列表
	 */
	public function getChildrenList($id,$field=array()){
		$field		= empty($field) ? array('id','name') : $field;
		$list		= $this->where("status='1' and  pid='$id'")->field($field)->select();
		return $list;
	}
	/**
	 * 获取指定分类子分类ID
	 * @param  string $cate 分类ID
	 * @return string       id列表
	 */
	public function getChildrenId($id){
		$field 		= 'id,pid,name';
		$category 	= $this->getTree($id, $field);
		$ids 		= array('in');
		$category	= $id > 0 ? array($category) : $category;
		$back_ids	= $this->SelectSonCatId($category);
		$ids		= !empty($ids) ? array_merge($ids,$back_ids) : $ids;
		return implode(',', $ids);
	}
	function SelectSonCatId($list){
		$ids		= array();
		if (!empty($list)){
			foreach ($list as $key => $value) {
				array_push($ids,$value['id']);
				if (!empty($value['_'])){
					$back_ids	= $this->SelectSonCatId($value['_']);
					$ids		= array_merge($ids,$back_ids);
				}
			}
		}
		return $ids;
	}

	/**
	 * 获取指定分类的同级分类
	 * @param  integer $id    分类ID
	 * @param  boolean $field 查询字段
	 * @return array
	 */
	public function getSameLevel($id, $field = true){
		$info = $this->info($id, 'pid');
		$map = array('pid' => $info['pid'], 'status' => 1);
		return $this->field($field)->where($map)->order('sort')->select();
	}
	/**
	 * 获取指定分类下的所有上级ID
	 */
	public function getParentId($pid = 0,$ids=array(0),$root=0){
		if ($pid == $root){
			return array(0);
		}else{
			$info	= $this->info($pid,'id,pid');
			$ids	= array_merge(array($info['id']),$ids);
			if (!empty($info) && $info['id'] > 0 && $info['pid'] > 0 && $info['pid'] != $root){
				return $this->getParentId($info['pid'],$ids,$root);
			}else{
				return $ids;
			}
		}
	}
	public function getParentName($pid = 0,$root){
		if ($pid == 0){
			return array();
		}else{
			$ids		= $this->getParentId($pid,array(),$root);
			if (empty($ids)){
				return array();
			}
			$list		= $this->where(array('id'=>array('in',$ids)))->field('id,name')->select();
			return $list;
		}
	}
}
