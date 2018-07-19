<?php

namespace Admin\Controller;

use Admin\Model\AuthGroupModel;
use Think\Model;

/**
 * 后台配置控制器
 */
class CommentController extends AdminController {

    /**
     * 修改成自己的
     * @author xxx
     */
    public function index() {
        $limit = 20;

        //获取数据
        $MainTab = 'comment';
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
            'user' => array('Ralias' => 'us', 'Ron' => 'us ON us.id=main.uid', 'Rfield' => array('nickname')),
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
            $map['main.uid'] = UID;
        }
        //排序
        $order = $MainAlias . '.id desc';
        $map['main.from'] =1;
        //检索字段
        $fields = (empty($MainField) ? $this->get_fields_string($MainModel->getDbFields(), $MainAlias) . ',' : $this->get_fields_string($MainField, $MainAlias) . ',') . $RelationFields;
        $fields = trim($fields, ',');
        $status=array('0'=>'未审核','1'=>'审核通过');
        
        //列表数据
        $list = $this->getLists($model, $map, $order, $fields, 1, $limit, true);
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                //数据格式化
                $list[$k]['status'] = $status[$v['status']];
                $list[$k]['create_time'] = $v['create_time'] > 0 ? date('Y-m-d H:i:s', $v['create_time']) : '--';
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
            $this->assign('SmallNav', array('评论管理', '评论列表'));
        } else {
            $cname[] = '评论管理';
            foreach ($ParentCatName as $v) {
                $cname[] = '评论列表';
            }
            $this->assign('SmallNav', $cname);
        }
        //记录当前列表页的cookie
        if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf'))
            Cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->display();
    }

   /**
     * 修改成自己的
     * @author xxx
     */
    public function reply() {

        $id=I('get.id','');
        $limit = 20;

        //获取数据
        $MainTab = 'comment';
        $MainAlias = 'main';
        $MainField = array();

        //主表模型
        $MainModel = M($MainTab)->alias($MainAlias);

        /*
         * 灵活定义关联查询
         * Ralias   关联表别名
         * Ron      关联条件
         * Rfield   关联查询字段，
         * */
        $RelationTab = array(
            'ucenter_member' => array('Ralias' => 'us', 'Ron' => 'us ON us.id=main.cid', 'Rfield' => array('username')),
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
            $map['main.uid'] = UID;
        }
        //排序
        $order = $MainAlias . '.id desc';
        $map['main.from'] =2;
        //检索字段
        $fields = (empty($MainField) ? $this->get_fields_string($MainModel->getDbFields(), $MainAlias) . ',' : $this->get_fields_string($MainField, $MainAlias) . ',') . $RelationFields;
        $fields = trim($fields, ',');
        $status=array('0'=>'未审核','1'=>'审核通过');
        //列表数据
        $list = $this->getLists($model, $map, $order, $fields, 1, $limit, true);
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                //数据格式化
                $list[$k]['status'] = $status[$v['status']];
                $list[$k]['create_time'] = $v['create_time'] > 0 ? date('Y-m-d H:i:s', $v['create_time']) : '--';
            }
        }
        $this->assign('_list', $list);
        //操作菜单,可以根据需要固定$menuid,$menuid为Menu表中的ID
        $menuid = 219;
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
            $this->assign('SmallNav', array('评论管理', '评论列表'));
        } else {
            $cname[] = '评论管理';
            foreach ($ParentCatName as $v) {
                $cname[] = '评论列表';
            }
            $this->assign('SmallNav', $cname);
        }
        //记录当前列表页的cookie
        if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf'))
            Cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->display();
    }


    /**
     * 修改成自己的
     * @author xxx
     */
    public function check_index() {
        $limit = 20;

        //获取数据
        $MainTab = 'comment';
        $MainAlias = 'main';
        $MainField = array();

        //主表模型
        $MainModel = M($MainTab)->alias($MainAlias);

        /*
         * 灵活定义关联查询
         * Ralias   关联表别名
         * Ron      关联条件
         * Rfield   关联查询字段，
         * */
        $RelationTab = array(
            'user' => array('Ralias' => 'us', 'Ron' => 'us ON us.id=main.uid', 'Rfield' => array('nickname')),
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
                'goodsname' => array('like', '%' . $keyword . '%'),
                '_logic' => 'OR',
            );
        }

        //状态检索
        $status = intval(I('get.find_status', 0));
        if (!empty($status) && $status > 0) {
            $map['main.status'] = $status;
        }else{
            $map['main.status'] = 0;            
        }
        if (UID != 1) {
            $map['main.uid'] = UID;
        }
        //排序
        $order = $MainAlias . '.id desc';
        $map['main.from'] =1;
        //检索字段
        $fields = (empty($MainField) ? $this->get_fields_string($MainModel->getDbFields(), $MainAlias) . ',' : $this->get_fields_string($MainField, $MainAlias) . ',') . $RelationFields;
        $fields = trim($fields, ',');
        $status=array('0'=>'未审核','1'=>'审核通过');
        //列表数据
        $list = $this->getLists($model, $map, $order, $fields, 1, $limit, true);
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                //数据格式化
                $list[$k]['status'] = $status[$v['status']];
                $list[$k]['create_time'] = $v['create_time'] > 0 ? date('Y-m-d H:i:s', $v['create_time']) : '--';
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
            $this->assign('SmallNav', array('评论管理', '评论列表'));
        } else {
            $cname[] = '评论管理';
            foreach ($ParentCatName as $v) {
                $cname[] = '评论列表';
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
    public function reply_edit($id = 0) {
        $info['id']=I('get.id','');
        //数据提交
        if (IS_POST)
            $this->reply_update();
        //页面数据
        if (false === $info) {
            $this->error('获取配置信息错误');
        }
        $this->assign('info', $info);

        //表单数据
        $FormData = $this->CustomerForm(1);
        $this->assign('FormData', $FormData);

        $this->NavTitle = '编辑配置';
        $this->display('raddedit');
    }

    /**
     * 编辑数据
     */
    public function reply_add($id = 0) {
        $id=I('get.id','');
        $info=M('comment')->where(array('id'=>$id))->find();
        //数据提交
        if (IS_POST)
            $this->re_update();
        //页面数据
        if (false === $info) {
            $this->error('获取配置信息错误');
        }
        $this->assign('info', $info);

        //表单数据
        $FormData = $this->CustomerForm(2);
        $this->assign('FormData', $FormData);

        $this->NavTitle = '编辑配置';
        $this->display('readdedit');
    }

    /**
     * 编辑数据
     */
    public function edit($id = 0) {
        //数据提交
        if (IS_POST)
            $this->update();
        //页面数据
        $info = M('comment')->field(true)->find($id);
        if (false === $info) {
            $this->error('获取配置信息错误');
        }
        $goods = M('goods')->where(array('id'=>$info['gid']))->find();
        $info['goodsname'] 	= $goods['goodsname'];
        $info['goodsimg'] 	= $goods['goodsimg'];
        $this->assign('info', $info);
        //表单数据
        $FormData = $this->CustomerForm(0);
        $this->assign('FormData', $FormData);

        $this->NavTitle = '编辑配置';
        $this->display('addedit');
    }

    //提交表单
    protected function update() {

        if (IS_POST) {
            // print_r($_POST);exit
            $Models = D('Comment');
            //print_r($_POST);exit();
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


    //提交表单
    protected function reply_update() {

        if (IS_POST) {
            $id       =I('id','');
            $info     =M('comment')->where(array('id'=>$id))->find();
            $content  =I('content');
            $data=array(
                'commentid'=>$id,
                'content'  =>$content,
                'stutus'   =>1,
                'cid'      =>UID,
                'from'     =>2,
                'oid'      =>$info['oid'],
                'gid'      =>$info['gid'],
                'create_time'=>NOW_TIME,
            );

            $res=M('comment')->data($data)->add();
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

    //提交表单
    protected function re_update() {

        if (IS_POST) {
            $id       =I('id','');
            $content  =I('content');

            $res=M('comment')->data(array('content'=>$content))->where(array('id'=>$id))->save();
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
        if (M('comment')->where($map)->delete()) {
            //记录行为
            action_log('config', $id, UID);
            //数据返回
            $this->success('删除成功', Cookie('__forward__'));
        } else {
            $this->error('删除失败！');
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

    protected function CustomerForm($index = 0) {


        $FormData[0] = array(
        	array('fieldName' => '商品名称', 'fieldValue' => 'goodsname', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
        	array('fieldName' => '商品图片', 'fieldValue' => 'goodsimg', 'fieldType' => 'images_show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '评论内容', 'fieldValue' => 'content', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
        	array('fieldName' => '评论图片', 'fieldValue' => 'pics', 'fieldType' => 'images_show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '评论星级', 'fieldValue' => 'assess', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '审核状态', 'fieldValue' => 'status', 'fieldType' => 'radio', 'isMust' => 0, 'fieldData' => array(0 => '不通过', 1 => '通过'), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '隐藏域', 'fieldValue' => array('id'), 'fieldType' => 'hidden', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
        );
        $FormData[1] = array(
 
            array('fieldName' => '回复内容', 'fieldValue' => 'content', 'fieldType' => 'textarea', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '隐藏域', 'fieldValue' => array('id'), 'fieldType' => 'hidden', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
        );

        $FormData[2] = array(
 
            array('fieldName' => '回复内容', 'fieldValue' => 'content', 'fieldType' => 'textarea', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
            array('fieldName' => '隐藏域', 'fieldValue' => array('id'), 'fieldType' => 'hidden', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
        );
        return $FormData[$index];
    }

}

?>