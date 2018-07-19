<?php
namespace Admin\Controller;

use User\Api\UserApi;
use Admin\Model\AuthRuleModel;
use Admin\Model\AuthGroupModel;
use Think\Model;

/**
 * 后台配置控制器
 */
class ShopPicController extends AdminController {

    /**
     * 修改成自己的
     */
    public function index() {
        $limit = 20;

        //获取数据
        $MainTab = 'business_shop_pic_order';
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
            'shop' => array('Ralias' => 'shop', 'Ron' => 'shop ON shop.id=main.shop_id', 'Rfield' => array('shop_name')),
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
        
        //关键词检索
        $keyword = trim(I('find_keyword', ''));
        if (!empty($keyword)) {
            $map['_complex'] = array(
                'shop.shop_name' => array('like', '%' . $keyword . '%'),
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

        $this->NavTitle = '商家管理';
        $ParentCatName = D('Category')->getParentName(3, 1);
        if (empty($ParentCatName)) {
            $this->assign('SmallNav', array('商家管理', '店家照片列表'));
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
    	if(!IS_ROOT && $this->group_id != 1){
    		$this->error('你没权限添加商家！');
    	}
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

	/**
	 * 	审核
	 */
	public function check($id = 0) {
		//数据提交
		if (IS_POST){
			$Models = M('business_shop_pic_order');
			//数据整理
			//.......
			$id 			= I('post.id',0,'intval');
			$status 		= I('post.status',0,'intval');
			//数据整理
			$shopPic 		= $Models->where(array('id'=>$id))->find();
			if($shopPic['status'] != 1){
				$this->error('已审核,请不要重复审核！');
			}
			$row 							= array();
			$row['status'] 					= $status;
			$res = $Models->where(array('id'=>$id))->save($row);
			if (false !== $res) {
				//数据返回
				$this->success('审核成功', Cookie('__forward__'));
			} else {
				$error = $Models->getError();
				$this->error(empty($error) ? '未知错误！' : $error);
			}
		}else{
			//页面数据
			$info = M('business_shop_pic_order')->field(true)->find($id);
	        if (false === $info) {
	            $this->error('获取配置信息错误');
	        }
	        $info['shop_name'] = M('shop')->where(array('id'=>$info['shop_id']))->getField('shop_name');
	        $this->assign('info', $info);
			 
			//表单数据
			$FormData = $this->CustomerForm(0);
			$this->assign('FormData', $FormData);
			 
			$this->NavTitle = '审核';
			$this->display('check');
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
        	array('fieldName' => '商家名称', 'fieldValue' => 'shop_name', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' =>$pidData, 'attrExtend' => 'placeholder=""'),
        	array('fieldName' => '鑫利豆', 'fieldValue' => 'xinlidou', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
        	array('fieldName' => '享利豆', 'fieldValue' => 'xianglidou', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
        	array('fieldName' => '总金额', 'fieldValue' => 'total_money', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
        	array('fieldName' => '支付金额', 'fieldValue' => 'pay_money', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
        	array('fieldName' => '图片张数', 'fieldValue' => 'pic_num', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
        	array('fieldName' => '添加时间', 'fieldValue' => 'create_time', 'fieldType' => 'show', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
        	array('fieldName' => '审核','fieldValue'=>'status','fieldType'=>'radio','isMust'=> 0,'fieldData'=>array('2'=>'通过','3'=>'不通过'),'attrExtend'=>''),
            array('fieldName' => '隐藏域', 'fieldValue' => array('id'), 'fieldType' => 'hidden', 'isMust' => 0, 'fieldData' => array(), 'attrExtend' => 'placeholder=""'),
        );
        return $FormData[$index];
    }

}

?>