<?php

namespace Admin\Controller;

use Admin\Model\AuthGroupModel;
use Think\Model;

/**
 * 后台配置控制器
 */
class BusinessDetailController extends AdminController {

    /**
     * 修改成自己的
     * @author xxx
     */
    public function index() {
        $limit = 20;

        //获取数据
        $MainTab = 'BusinessDetail';
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
            'scategory' => array('Ralias' => 'cate', 'Ron' => 'cate ON cate.id=main.category_id', 'Rfield' => array('name as categoryname')),
            'ucenter_member' => array('Ralias' => 'uc', 'Ron' => 'uc ON uc.id=main.businessid', 'Rfield' => array('username')),
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
        //关键词检索
        $keyword = trim(I('find_keyword', ''));
        if (!empty($keyword)) {
            $map['_complex'] = array(
                'main.name' => array('like', '%' . $keyword . '%'),
                '_logic' => 'OR',
            );
        }
        //排序
        $order = $MainAlias . '.id desc';

        //检索字段
        $fields = (empty($MainField) ? $this->get_fields_string($MainModel->getDbFields(), $MainAlias) . ',' : $this->get_fields_string($MainField, $MainAlias) . ',') . $RelationFields;
        $fields = trim($fields, ',');

        //列表数据
        $list = $this->getLists($model, $map, $order, $fields, 1, $limit, true);
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                //数据格式化
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
            $this->assign('SmallNav', array('商家管理', '资料列表'));
        } else {
            $cname[] = '商家管理';
            foreach ($ParentCatName as $v) {
                $cname[] = '资料列表';
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
        //页面数据
        $info = M('BusinessDetail')->field(true)->find($id);
        if (false === $info) {
            $this->error('获取配置信息错误');
        }
        if(!empty($info['longitude']) && !empty($info['latitude'])){
        	$gcj02tobd09 		= gcj02tobd09($info['longitude'], $info['latitude']);
        	$info['longitude'] 	= $gcj02tobd09['longitude'];
        	$info['latitude'] 	= $gcj02tobd09['latitude'];
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
        if (M('BusinessDetail')->where($map)->delete()) {
            //记录行为
            action_log('config', $id, UID);
            //数据返回
            $this->success('删除成功', Cookie('__forward__'));
        } else {
            $this->error('删除失败！');
        }
    }


    //提交表单
    protected function update() {

        if (IS_POST) {
            $Models = D('BusinessDetail');
            //print_r($_POST);exit();
            //数据整理
            //.......
            //数据整理
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
        
        $menus              = D('Scategory')->field(array('id','name','pid'))->where($map)->select();
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
        $pidData = array_merge(array(0=>array('category_id'=>0,'name'=>'-请选择-')), $pidData);
        // print_r($pidData);exit;
        $attr=M('ucenter_member')->field('id,username as name')->join('left join duoduo_auth_group_access on duoduo_auth_group_access.uid=duoduo_ucenter_member.id')->where(array('group_id'=>4))->select();

        $attr = array_merge(array(0=>array('id'=>0,'name'=>'-请选择-')), $attr);
        $FormData[0] = array(
            array('fieldName' => '商家名称', 'fieldValue' => 'name', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入商品名称"'),
            array('fieldName' => '商家电话', 'fieldValue' => 'mobile', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入商家电话"'),
            array('fieldName' => '商家分类', 'fieldValue' => 'category_id', 'fieldType' => 'select2', 'isMust' => 1, 'fieldData' =>$pidData, 'attrExtend' => 'placeholder="请选择商家分类"'),
            array('fieldName' => '商家ID', 'fieldValue' => 'businessid', 'fieldType' => 'select2', 'isMust' => 1, 'fieldData' =>$attr, 'attrExtend' => 'placeholder="请选择商家ID"'),
            array('fieldName'=>  '商家封面','fieldValue'=>'face','fieldType'=>'image','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'data-table="goods" data-field="images" data-size=""'),
            array('fieldName'=>  '轮播图','fieldValue'=>'pics','fieldType'=>'images','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'data-table="goods" data-field="images" data-size=""'),
            array('fieldName'=>  '商家推荐图','fieldValue'=>'recommentpic','fieldType'=>'image','isMust'=>1,'fieldData'=>array(),'attrExtend'=>'data-table="goods" data-field="image" data-size=""'),
        	array('fieldName' => '是否轮播', 'fieldValue' => 'is_slide', 'fieldType' => 'radio', 'isMust' => 0, 'fieldData' => array(1 => '是', 0 => '否'), 'attrExtend' => 'placeholder=""'),
        	array('fieldName' => '店铺详情', 'fieldValue' => 'content', 'fieldType' => 'editor', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder="" rows="5" style="height:100%;"'),
            array('fieldName' => '地图定位', 'fieldValue' => 'position', 'fieldType' => 'position', 'isMust' => 1, 'fieldData' => array('type' => 3), 'attrExtend' => ''),
        	array('fieldName'=>'排序','fieldValue'=>'sort','fieldType'=>'text','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder="用于显示的顺序"'),
            array('fieldName' => '隐藏域', 'fieldValue' => array('id'), 'fieldType' => 'hidden', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
        );
        return $FormData[$index];
    }

}

?>