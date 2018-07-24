<?php
namespace Admin\Controller;

use User\Api\UserApi;
use Admin\Model\AuthRuleModel;
use Admin\Model\AuthGroupModel;
use Think\Model;

/**
 * 后台配置控制器
 */
class ShopController extends AdminController {

    /**
     * 修改成自己的
     */
    public function index() {
        $limit = 20;

        //获取数据
        $MainTab = 'Shop';
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
            //'scategory' => array('Ralias' => 'cate', 'Ron' => 'cate ON cate.id=main.category_id', 'Rfield' => array('name as categoryname')),
        );
        $RelationTab = $this->getRelationTab($RelationTab);
        $tables = $RelationTab['tables'];
        $RelationFields = $RelationTab['fields'];
        $model = !empty($tables) ? $MainModel->join($tables, 'LEFT') : $MainModel;

        //检索条件
        $map = array();
        
        //当前用户的店铺
        /* if(!IS_ROOT && $this->group_id != 1){
        	$map['uid'] 			= UID;
        } */

        //时间区间检索
        $create_time				= time_between('create_time', 'main');
        $map						= array_merge($map,$create_time);
        
        $county = intval(I('county', '0'));
        if ($county > 0) {
        	$map['county'] = $county;
        }
        
        //关键词检索
        $keyword = trim(I('find_keyword', ''));
        if (!empty($keyword)) {
            $map['_complex'] = array(
                'main.shop_name' => array('like', '%' . $keyword . '%'),
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
                $user 						= M('user')->where(array('id'=>$v['uid']))->find();
            	$list[$k]['phone'] 			= $user['phone'];
                $list[$k]['create_time'] 	= $v['create_time'] > 0 ? date('Y-m-d H:i:s', $v['create_time']) : '--';
                $list[$k]['verify_time'] 	= $v['verify_time'] > 0 ? date('Y-m-d H:i:s', $v['verify_time']) : '--';
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
            $this->assign('SmallNav', array('商家管理', '商家列表'));
        } else {
            $cname[] = '商家管理';
            foreach ($ParentCatName as $v) {
                $cname[] = '商家列表';
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
        $info = M('Shop')->field(true)->find($id);
        if (false === $info) {
            $this->error('获取配置信息错误');
        }
        /* if(!empty($info['longitude']) && !empty($info['latitude'])){
        	$gcj02tobd09 		= gcj02tobd09($info['longitude'], $info['latitude']);
        	$info['longitude'] 	= $gcj02tobd09['longitude'];
        	$info['latitude'] 	= $gcj02tobd09['latitude'];
        } */
        $info['phone'] = M('user')->where(array('id'=>$info['uid']))->getField('phone');
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
    	if(!IS_ROOT && $this->group_id != 1){
    		$this->error('你没权限删除商家！');
    	}
        $ids = I('request.ids');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        $ids = is_array($ids) ? $ids : array(intval($ids));
        $ids = array_unique($ids);
        $map = array('id' => array('in', $ids));
        if (M('Shop')->where($map)->delete()) {
            //记录行为
            action_log('config', $id, UID);
            //数据返回
            $this->success('删除成功', Cookie('__forward__'));
        } else {
            $this->error('删除失败！');
        }
    }
    
    protected function addMember(){
    	$username 		= I('post.username', '', 'trim');
    	$password 		= I('post.password', '');
    	$repassword 	= I('post.repassword', '');
    	$gid 			= 2;
    	/* 检测用户名 */
    	if($username == ''){
    		$this->error('请输入用户登录手机号！');
    	}
    	if (!Mobile_check($username, array(1,2,3,4))) {
    		$this->error('请输入正确格式的用户登录手机号');
    	}
    	$count = M('ucenterMember')->where(array('username'=>$username))->count();
    	if($count>0) $this->error('用户登录手机号已经存在！');
    	 
    	/* 检测密码 */
    	if($password == '') $this->error('请输入登录密码！');
    	if ($password != $repassword) $this->error('密码和确认密码不一致！');
    	 
    	/* 调用注册接口注册用户 */
    	$User = new UserApi;
    	$uid = $User->register($username, $password);
    	if (0 < $uid) { //注册成功
    		$user = array('uid' => $uid, 'nickname' => $username, 'status' => 1);
    		if (!M('Member')->add($user)) {
    			$this->error('用户添加失败！');
    		} else {
    			if ($uid > 0 && $gid > 0) {
    				//根据分组添加用户
    				$AuthGroup = D('AuthGroup');
    				if ($gid && !$AuthGroup->checkGroupId($gid)) {
    					$this->error($AuthGroup->error);
    				}
    				if ($AuthGroup->addToGroup($uid, $gid)) {
    					return $uid;
    				}
    			}else{
    				return false;
    			}
    		}
    	}
    }


    //提交表单
    protected function update() {

        if (IS_POST) {
        	$id = I('post.id', 0, 'intval');
            $Models = D('Shop');
            //数据整理
            //.......
            //数据整理
            $res = $Models->update();
            if (false !== $res) {
                //记录行为
                action_log('config', $res['id'], UID);
                //数据返回
                $this->success($res['ac'] > 0 ? '更新成功' : '新增成功', Cookie('__forward__'));
            } else {
                $error = $Models->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        }
        $this->error('非法提交！');
    }
    
    //推荐首页
    public function recommend() {
    	$ids = I('request.ids');
    	if (empty($ids)) {
    		$this->error('请选择要操作的数据!');
    	}
    	$ids = is_array($ids) ? $ids : array(intval($ids));
    	$ids = array_unique($ids);
    	$map = array('id' => array('in', $ids));
    	if (M('shop')->where($map)->setField('is_recommend', 1)) {
    		//记录行为
    		action_log('shop', $ids, UID);
    		//数据返回
    		$this->success('推荐成功', Cookie('__forward__'));
    	} else {
    		$this->error('推荐失败！');
    	}
    }
    
    //取消推荐首页
    public function recommendCancel() {
    	$ids = I('request.ids');
    	if (empty($ids)) {
    		$this->error('请选择要操作的数据!');
    	}
    	$ids = is_array($ids) ? $ids : array(intval($ids));
    	$ids = array_unique($ids);
    	$map = array('id' => array('in', $ids));
    	if (M('shop')->where($map)->setField('is_recommend', 0)) {
    		//记录行为
    		action_log('shop', $ids, UID);
    		//数据返回
    		$this->success('取消推荐成功', Cookie('__forward__'));
    	} else {
    		$this->error('取消推荐失败！');
    	}
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

        $FormData[0] = array(
        	array('fieldName' => '商家名称', 'fieldValue' => 'shop_name', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入商家名称"'),
        	array('fieldName' => '联系方式', 'fieldValue' => 'mobile', 'fieldType' => 'text', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'placeholder="请输入联系方式"'),
        	array('fieldName' => '商家封面', 'fieldValue' => 'face', 'fieldType' => 'image', 'isMust' => 1, 'fieldData' => array(), 'attrExtend' => 'data-table="avatar" data-field="pic" data-size=""'),
        	array('fieldName' => '地图定位', 'fieldValue' => 'position', 'fieldType' => 'position', 'isMust' => 1, 'fieldData' => array('type' => 3), 'attrExtend' => ''),
        	array('fieldName' => '排序', 'fieldValue'=>'sort', 'fieldType'=>'text','isMust'=>0,'fieldData'=>array(),'attrExtend'=>'placeholder="用于显示的顺序"'),
        	array('fieldName' => '状态', 'fieldValue'=>'status', 'fieldType'=>'radio','isMust'=>0,'fieldData'=>array('1'=>'启用','2'=>'禁用'),'attrExtend'=>''),
        	array('fieldName' => '隐藏域', 'fieldValue' => array('id'), 'fieldType' => 'hidden', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
        );
        return $FormData[$index];
    }

}

?>