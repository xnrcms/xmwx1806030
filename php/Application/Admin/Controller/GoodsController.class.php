<?php

namespace Admin\Controller;

use Admin\Model\AuthGroupModel;
use Think\Model;

/**
 * 后台配置控制器
 */
class GoodsController extends AdminController {

    /**
     * 修改成自己的
     * @author xxx
     */
    public function index() {
    	//分类
    	$classify = $this->getClassify();
    	$classifyArr = array();
    	if(!empty($classify)){
    		foreach ($classify as $key=>$value){
    			$classifyArr[$value['id']] = $value['name'];
    		}
    	}
    	$this->assign('classifyArr', $classifyArr);
    	
        $limit = 20;
        //获取数据
        $MainTab = 'Goods';
        $MainAlias = 'main';
        $MainField = array();

        //主表模型
        $MainModel = M($MainTab)->alias($MainAlias);

        /*
         * 灵活定义关联查询
         * Ralias 	关联表别名
         * Ron    	关联条件
         * Rfield	关联查询字段，
         * */
        $RelationTab = array(
            'category' => array('Ralias' => 'cate', 'Ron' => 'cate ON cate.id=main.category_id', 'Rfield' => array('name as categoryname')),
        );
        $RelationTab = $this->getRelationTab($RelationTab);
        $tables = $RelationTab['tables'];
        $RelationFields = $RelationTab['fields'];
        $model = !empty($tables) ? $MainModel->join($tables, 'LEFT') : $MainModel;

        //检索条件
        $map = array();

        //时间区间检索
        $create_time_s = I('create_time_s', '');
        $create_time_e = I('create_time_e', '');
        if ($create_time_s !== "" && $create_time_e !== "") {
            $create_stime = $create_time_s . ' 00:00:00';
            $create_etime = $create_time_e . ' 23:59:59';
            $create_time_s = strtotime($create_stime);
            $create_time_e = strtotime($create_etime);
            $map['main.create_time'] = array(array('egt', $create_time_s), array('elt', $create_time_e));
        }
        
        //商品分类
        $category_id = intval(I('get.category_id', 0));
        if (!empty($category_id) && $category_id > 0) {
        	$map['main.category_id'] = $category_id;
        }
        
        //商品特性
    	$goodsType = intval(I('get.goods_type', 0));
        if (!empty($goodsType) && $goodsType > 0) {
            $map['main.goodstype'] = $goodsType;
        }
        
        //关键词检索
        $keyword = trim(I('find_keyword', ''));
        if (!empty($keyword)) {
            $map['_complex'] = array(
                'goodsname' => array('like', '%' . $keyword . '%'),
                '_logic' => 'OR',
            );
        }

        //状态检索
        $status = intval(I('get.find_status', 0));
        if (!empty($status) && $status > 0) {
            $map['main.status'] = $status;
        }
        if (UID != 1) {
            $this->error('您没有权限！');
        }
        //排序
        $order = $MainAlias . '.id desc';

        //检索字段
        $fields = (empty($MainField) ? $this->get_fields_string($MainModel->getDbFields(), $MainAlias) . ',' : $this->get_fields_string($MainField, $MainAlias) . ',') . $RelationFields;
        $fields = trim($fields, ',');

        //列表数据
        $status = array('删除', '上架', '下架');
        $attr   = array('','积分专区','现金专区','折扣专区');
        $list = $this->getLists($model, $map, $order, $fields, 1, $limit, true);
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                //数据格式化
                $list[$k]['goodstype'] = $attr[$v['goodstype']];
                $list[$k]['status'] = $status[$v['status']];
                $list[$k]['create_time'] = $v['create_time'] > 0 ? date('Y-m-d H:i:s', $v['create_time']) : '--';
                $list[$k]['update_time'] = $v['update_time'] > 0 ? date('Y-m-d H:i:s', $v['update_time']) : '--';
            }
        }
        $this->assign('_list', $list);
        //操作菜单,可以根据需要固定$menuid,$menuid为Menu表中的ID
        $menuid = $this->menuid;
        $SonMenu = $this->getSonMenu($menuid);
        $this->assign('ListTopNav', !empty($SonMenu['TOPMENU']) ? $SonMenu['TOPMENU'] : array());
        $this->assign('ListRightNav', !empty($SonMenu['RIGHTMENU']) ? $SonMenu['RIGHTMENU'] : array());

        //代码扩展
        $this->extends_param .= $this->extends_param . '&cate_id=' . I('get.cate_id');
        //.........
        //代码扩展

        $this->NavTitle = '配置管理';
        $ParentCatName = D('Category')->getParentName(3, 1);
        if (empty($ParentCatName)) {
            $this->assign('SmallNav', array('商品管理', '商品列表'));
        } else {
            $cname[] = '商品管理';
            foreach ($ParentCatName as $v) {
                $cname[] = '商品列表';
            }
            $this->assign('SmallNav', $cname);
        }
        //记录当前列表页的cookie
        if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf'))
            Cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->display();
    }

    /**
     * 新增数据
     */
    public function add() {
        //数据提交
        if (IS_POST)
            $this->update();

        //表单数据
        $FormData = $this->CustomerForm(0);
        $this->assign('FormData', $FormData);

        $this->NavTitle = '新增配置';
        $this->display('addedit');
    }

    /**
     * 编辑数据
     */
    public function edit($id = 0) {
        //数据提交
        if (IS_POST)
            $this->update();
        $this->catid = I('get.cate_id');
        //页面数据
        $info = M('Goods')->field(true)->find($id);
        $info['paytime'] = $info['paytime'] > 0 ? date('Y-m-d H:i:s', $info['paytime']) : '--';
        if (false === $info) {
            $this->error('获取配置信息错误');
        }
        $this->assign('info', $info);

        //表单数据
        $FormData = $this->CustomerForm(0);
        $this->assign('FormData', $FormData);

        $this->NavTitle = '编辑配置';
        $this->display('addedit');
    }

    /**
     * 删除数据
     */
    public function del() {
        $ids = I('request.ids');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        $ids = is_array($ids) ? $ids : array(intval($ids));
        $ids = array_unique($ids);
        $map = array('id' => array('in', $ids));
        if (M('Goods')->where($map)->delete()) {
            //记录行为
            action_log('config', $id, UID);
            //数据返回
            $this->success('删除成功', Cookie('__forward__'));
        } else {
            $this->error('删除失败！');
        }
    }

    public function resume() {
        $ids = I('request.ids');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        $ids = is_array($ids) ? $ids : array(intval($ids));
        $ids = array_unique($ids);
        $map = array('id' => array('in', $ids));
        if (M('Goods')->where($map)->setField('status', 1)) {
            //记录行为
            action_log('config', $id, UID);
            //数据返回
            $this->success('上架成功', Cookie('__forward__'));
        } else {
            $this->error('上架失败！');
        }
    }

    public function forbid() {
        $ids = I('request.ids');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        $ids = is_array($ids) ? $ids : array(intval($ids));
        $ids = array_unique($ids);
        $map = array('id' => array('in', $ids));
        if (M('Goods')->where($map)->setField('status', 2)) {
            //记录行为
            action_log('config', $id, UID);
            //数据返回
            $this->success('下架成功', Cookie('__forward__'));
        } else {
            $this->error('下架失败！');
        }
    }


    //提交表单
    protected function update() {

        if (IS_POST) {
            $Models = D('Goods');
            //print_r($_POST);exit();
            //数据整理
            //.......
            //数据整理
            $_POST['paytime']= strtotime($_POST['paytime']);
            $res = $Models->update();
            if (false !== $res) {
                //记录行为
                action_log('config', $data['id'], UID);
                //数据返回
                $this->success($res['ac'] > 0 ? '更新成功' : '新增成功', Cookie('__forward__'));
            } else {
                $error = $Models->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        }
        $this->error('非法提交！');
    }

    //获取分类
    protected function getClassify(){
        $map                = array();
        // $map['classifyId']    = array('not in',array('1','7','17'));
        $map['status']=1;
        
        $menus              = D('Category')->field(array('id','name','pid'))->where($map)->select();
        $menus              = D('Common/Tree')->toFormatTree($menus,'name','id','pid');
        
        $pidData            = array();
        if (!empty($menus)){
            foreach ($menus as $k=>$v){
                $pidData[$k]    = array('id'=>$v['id'],'name'=>$v['title_show']);
            }
        }
        return $pidData;
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

    protected function CustomerForm($index = 0) {
        $pidData = $this->getClassify();
        $pidData = array_merge(array(0=>array('id'=>0,'name'=>'-请选择-')), $pidData);
        $attr=M('attr')->field('id,name')->select();
        $attr = array_merge(array(0=>array('id'=>0,'name'=>'-请选择-')), $attr);
        $FormData[0] = array(
            array('fieldName' => '商品名称', 'fieldValue' => 'goodsname', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入商品名称"'),
            array('fieldName' => '商品分类', 'fieldValue' => 'category_id', 'fieldType' => 'select2', 'isMust' => 1, 'fieldData' => $pidData, 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '商品原价', 'fieldValue' => 'originalprice', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入商品原价"'),
            array('fieldName' => '商品价格', 'fieldValue' => 'goodsprice', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入商品价格"'),
            array('fieldName' => '鑫豆抵扣(%)', 'fieldValue' => 'percentage', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入鑫豆抵扣百分比"'),
        	array('fieldName' => '快递费', 'fieldValue' => 'express_fee', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入快递费"'),
            //array('fieldName' => '销量', 'fieldValue' => 'salenum', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入销量"'),
            array('fieldName' => '商品封面', 'fieldValue' => 'goodsimg', 'fieldType' => 'image', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'data-table="goods" data-field="goodsimg" data-size=""'),
            array('fieldName' => '商品相册','fieldValue'=>'goodsimgs','fieldType'=>'images','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'data-table="goods" data-field="images" data-size=""'),
            array('fieldName' => '商品排序', 'fieldValue' => 'goodssorts', 'fieldType' => 'text', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="默认为0,数字越大越靠前"'),
            array('fieldName' => '商品状态', 'fieldValue' => 'status', 'fieldType' => 'radio', 'isMust' => 0, 'fieldData' => array(1 => '上架', 2 => '下架'), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '是否推荐首页', 'fieldValue' => 'is_recommend', 'fieldType' => 'radio', 'isMust' => 0, 'fieldData' => array(1 => '推荐 ', 0 => '不推荐'), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '商品详情', 'fieldValue' => 'content', 'fieldType' => 'editor', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="" rows="5" style="height:100%;"'),
            //array('fieldName' => '点击量', 'fieldValue' => 'hit', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入点击量"'),
            array('fieldName' => '隐藏域', 'fieldValue' => array('id'), 'fieldType' => 'hidden', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
        );
        return $FormData[$index];
    }

}

?>