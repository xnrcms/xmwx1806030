<?php
namespace admin\Controller;
use Think\Controller;
// 系统控制器
class SystemController extends BaseController {
    /**
     * [setting 系统设置]
     * @author TF <[2281551151@qq.com]>
     */
    public function setting() {
    	if ( IS_POST ) {
    		$data   = array();
    		$post   = I('post.');
    		$config = M('config');
    		foreach ($post as $key => $value) {
    			$config->where(array('config_sign'=>$key))->data(array('config_value'=>$value))->save();
    		}
    		$this->success('保存成功！');
    	} else {
	    	$config 	= M('config');
	    	$configList = $config->where(array('status'=>1))->order('sort')->select();

	    	$this->assign('configList', $configList);
	    	$this->display('setting');
    	}
    }
    
    /*
     * 简介
     */
    public function introduction(){
            //当前用户代理agent_id
            $connectiont = array(
                'db_type' => 'sqlsrv',
                'db_host' => '120.27.241.211',
                'db_user' => 'sa',
                'db_pwd' => 'HZxm123456...',
                'db_port' => 9428,
                'db_name' => 'QPPlatformDB',
                'db_charset' => 'utf8',
            );
            $sqlsrv_model   =   M('WeiXinInfo',NULL,$connectiont);
            if(IS_POST){
                $FeedbaceMail   =   I('post.FeedbaceMail','');
                $where          =   array('1=1');
                $WeiXinInfo    = $sqlsrv_model->table('WeiXinInfo')->where($where)->find();
                if(!empty($WeiXinInfo)){
                    $res    =   $sqlsrv_model->table('WeiXinInfo')->where($where)->setField(array('FeedbaceMail'=>$FeedbaceMail));
                    $this->success('保存成功！');
                }  else {
                    $this->success('保存失败！');
                }
            }  else {
                $where          =   array('1=1');
                $sqlsrv_model   =   M('WeiXinInfo',NULL,$connectiont);
                $FeedbaceMail    = $sqlsrv_model->table('WeiXinInfo')->where($where)->getField('FeedbaceMail');
	    	$this->assign('FeedbaceMail', rtrim($FeedbaceMail));
//                dump($FeedbaceMail);die;
	    	$this->display();
            }
    }
    
    /*
     * 公告
     */
    public function notice(){
            //当前用户代理agent_id
            $connectiont = array(
                'db_type' => 'sqlsrv',
                'db_host' => '120.27.241.211',
                'db_user' => 'sa',
                'db_pwd' => 'HZxm123456...',
                'db_port' => 9428,
                'db_name' => 'QPPlatformDB',
                'db_charset' => 'utf8',
            );
            $sqlsrv_model   =   M('WeiXinInfo',NULL,$connectiont);
            if(IS_POST){
                $GameWeixin   =   I('post.GameWeixin','');
                $where          =   array('1=1');
                $WeiXinInfo    = $sqlsrv_model->table('WeiXinInfo')->where($where)->find();
                if(!empty($WeiXinInfo)){
                    $res    =   $sqlsrv_model->table('WeiXinInfo')->where($where)->setField(array('GameWeixin'=>$GameWeixin));
                    $this->success('保存成功！');
                }  else {
                    $this->success('保存失败！');
                }
            }  else {
                $where          =   array('1=1');
                $sqlsrv_model   =   M('WeiXinInfo',NULL,$connectiont);
                $GameWeixin    = $sqlsrv_model->table('WeiXinInfo')->where($where)->getField('GameWeixin');
	    	$this->assign('GameWeixin', rtrim($GameWeixin));
//                dump($FeedbaceMail);die;
	    	$this->display();
            }
    }
    
    
    
    //微信公众号图文采集
    public function collectionWeixinNewsMaterial(){
        $agentId    = 8;
        $page       =   I('post.page',0);
        $limit      =   30;
        $offset     =   $page*$limit;
        $param =   array();
        $param['offset']    =   $offset;
        $param['count']     =   $limit;
        $news_arr   =   R('Home/Weixin/GetWxNewsMaterial',$param);
//        dump($news_arr['content']);die;
        if(!empty($news_arr['content'])){
            $media_ids  =   array();
            foreach ($news_arr['content'] as $key => $value) {
                $media_ids[]    =   $value['media_id'];
            }
            if(!empty($media_ids)){
                
                $where  =   array();
                $where['uid']       =   $agentId;
                $where['media_id']  =   array('in',$media_ids);
                $delete_list    =   M('public_material_news')->where($where)->field('id,title')->select();
                $material_news_ids  =   array();
                foreach ($delete_list as $key => $value) {
                    $material_news_ids[]    =   $value['id'];
                }
                
                //删除素材
                if(!empty($material_news_ids)){
                    M('public_material_news')->where(array('id'=>array('in',$material_news_ids)))->delete();
                    M('public_material_news_detail')->where(array('material'=>array('in',$material_news_ids)))->delete();
                }
            }
            
            //重新添加
            $news_list  =   $news_arr['content'];
            $news_list  =   array_reverse($news_list);
            foreach ($news_list as $key => $value) {
                $material_news_data =   array();
                $material_news_data['uid']          =   $agentId;
                $material_news_data['title']        =   $value['items'][0]['title'];
                $material_news_data['create_time']  =   $value['create_time'];
                $material_news_data['update_time']  =   $value['update_time'];
                $material_news_data['media_id']     =   $value['media_id'];
                $material_news_data['cover_image']  =   $value['items'][0]['thumb_url'];
                $news_id    =   M('public_material_news')->add($material_news_data);
                if($news_id){
                    $material_news_detail_data  =   array();
                    foreach ($value['items'] as $k2 => $items) {
                        $material_news_detail_data[$k2]['material'] =   $news_id;
                        $material_news_detail_data[$k2]['title']    =   $items['title'];
                        $material_news_detail_data[$k2]['pic']      =   $items['thumb_url'];
                        $material_news_detail_data[$k2]['url']      =   $items['url'];
                        $material_news_detail_data[$k2]['desc']     =   $items['digest'];
                    }
                    M('public_material_news_detail')->addAll($material_news_detail_data);
                }
            }
        }
        die(json_encode(array('code'=>1,'message'=>'调用成功','result'=>$news_list)));
    }
    
    //微信公众号图片素材采集
    public function collectionWeixinImageMaterial(){
        $agentId    = 8;
//            $agentId=8;
        $where  =   array();
        $where['uid']       =   $agentId;
        $old_material_image_list    =   M('public_material_image')->where($where)->field('id,media_id')->select();
        $old_media_ids  =   array();
        if(!empty($old_material_image_list)){
            foreach ($old_material_image_list as $key => $value) {
                $old_media_ids[]    =   $value['media_id'];
            }
        }
            
        $page       =   I('post.page',0);
        $limit      =   100;
        $offset     =   $page*$limit;
        $param =   array();
        $param['offset']    =   $offset;
        $param['count']     =   $limit;
        $news_arr   =   R('Home/Weixin/GetWxImageMaterial',$param);
//        dump($news_arr['content']);die;
        $news_list  =   array();
        $news_list  =   $news_arr['content'];
        $news_list  =   array_reverse($news_list);
        if(!empty($news_list)){
            $new_media_ids  =   array();
            foreach ($news_list as $key => $value) {
                $new_media_ids[]    =   $value['media_id'];
            }
            
            $media_ids  = array_diff($new_media_ids, $old_media_ids);
//                dump($media_ids);die;
            if(!empty($media_ids)){
            
//                dump($news_list);die;
                $material_image_data  =   array();
                foreach ($news_list as $key => $value) {
                    if(in_array($value['media_id'], $media_ids)){
                            $material_image_data[$key]['uid']         =   $agentId;
                            $material_image_data[$key]['name']        =   $value['name'];
                            $material_image_data[$key]['media_id']    =   $value['media_id'];
                            $material_image_data[$key]['update_time'] =   $value['update_time'];
                            $material_image_data[$key]['cover_image'] =   $value['cover_image'];
                    }
                }
//                dump($material_image_data);die;
                if(!empty($material_image_data)){
                    M('public_material_image')->addAll($material_image_data);
                }
            }
        }
        die(json_encode(array('code'=>1,'message'=>'调用成功','result'=>$news_list)));
    }
    
    //选择图文素材
    public function getWxNewsMaterial(){
        $agentId    =   8;
        $where  =   array();
        $where['uid']   = $agentId;
        $material_news_list =   M('public_material_news')->where($where)->field("id,title,cover_image,create_time,update_time,media_id")->order('id DESC')->select();
        if(!empty($material_news_list)){
            foreach ($material_news_list as $key => $value) {
                $material_news_list[$key]['create_time']  = date('Y年m月d日',$value['create_time']);
                $material_news_list[$key]['update_time']  = date('Y年m月d日',$value['update_time']);
            }
        }
        die(json_encode(array('code'=>1,'message'=>'调用成功','result'=>$material_news_list)));
    }
    
    //选择图片素材
    public function getWxImageMaterial(){
        $agentId    =   8;
        $where  =   array();
        $where['uid']   = $agentId;
        $material_image_list =   M('public_material_image')->where($where)->order('id DESC')->select();
        if(!empty($material_image_list)){
            foreach ($material_image_list as $key => $value) {
                $material_news_list[$key]['update_time']  = date('Y年m月d日',$value['update_time']);
            }
        }
        die(json_encode(array('code'=>1,'message'=>'调用成功','result'=>$material_image_list)));
    }
    
    
    
    //发布菜单
    public function publishMenu(){
        $news_arr   =   R('Home/Weixin/createMenu');
        die(json_encode($news_arr));
    }
    
    
    
    //自动回复
    public function weixinReply(){
            $uid    = 8;
            $where  =   array();
            $where['uid']       =   $uid;
            $list =   M('public_keywords')->where($where)->select();
            
            $index_reply_type   =   array(
                0   =>  '文本',
                1   =>  '图片',
                2   =>  '图文'
            );
            $status_text    =   array(
                0   =>  '关闭',
                1   =>  '启用'
            );
            foreach ($list as $key => $value) {
                $list[$key]['status_text']  =   $status_text[$value['status']];
            }
//            dump($list);die;
            $this->assign('index_reply_type', $index_reply_type);
            $this->assign('list', $list);
            $this->display('weixin_reply');
    }
    
    //添加关键词回复
    public function weixin_reply_select(){
        
            $index_reply_type   =   array(
                0   =>  '文本',
//                1   =>  '图片',
                2   =>  '图文'
            );
            $this->assign('index_reply_type',$index_reply_type);
            $this->display();
    }
    
    
    //微信
    public function weixinReplyAdd() {
            $uid    = 8;
            $type   =   I('get.type',0,'intval');
            $index_reply_type   =   array(
                0   =>  '文本',
                1   =>  '图片',
                2   =>  '图文'
            );
            $reply_type =   $index_reply_type[$type];
            $this->assign('reply_type',$reply_type);
            $this->assign('type',$type);

            switch ($type) {
                case 0: //文本
                    $this->display('weixin_reply_add_text');
                    break;
                case 1: //图片
                    $this->display('weixin_reply_add_image');
                    break;
                case 2: //图文
                    $this->display('weixin_reply_add_news');
                    break;

                default:
                    break;
            }
        
    }
    
    
    //微信
    public function weixinReplyEdit() {
            $uid    = 8;
            
            $id     =   I('get.id',0,'intval');
            $where  =   array();
            $where['id']    =   $id;
            $weixin_reply  =   M('public_keywords')->where($where)->find();
            if(!empty($weixin_reply) && $weixin_reply['uid'] != $uid){
                $this->error('你无权操作当前信息', U('weixinReplyAdd'));
            }
            $index_reply_type   =   array(
                0   =>  '文本',
                1   =>  '图片',
                2   =>  '图文'
            );
            $reply_type =   $index_reply_type[$weixin_reply['reply_type']];
            $this->assign('reply_type',$reply_type);
            $this->assign('type',$weixin_reply['reply_type']);

            switch ($weixin_reply['reply_type']) {
                case 0: //文本
                    if(!empty($weixin_reply['reply_text'])){
                        $weixin_reply['reply_text'] = base64_decode($weixin_reply['reply_text']);
                    }
                    $this->assign('weixin_reply',$weixin_reply);
                    $this->display('weixin_reply_add_text');
                    break;
                case 1: //图片
                    if(!empty($weixin_reply['reply_media_id'])){
                        
                        $material_image  =   M('public_material_image')->where(array('media_id'=>$weixin_reply['reply_media_id']))->find();
                        $weixin_reply['reply_media_title']  =   $material_image['name'];
                    }
                    $this->assign('weixin_reply',$weixin_reply);
                    $this->display('weixin_reply_add_image');
                    break;
                case 2: //图文
                    if(!empty($weixin_reply['reply_media_id'])){
                        
                        $material_news  =   M('public_material_news')->where(array('media_id'=>$weixin_reply['reply_media_id']))->find();
                        $weixin_reply['reply_media_title']  =   $material_news['title'];
                    }
//                    dump($material_news);die;
                    $this->assign('weixin_reply',$weixin_reply);
                    $this->display('weixin_reply_add_news');
                    break;

                default:
                    break;
            }
        
    }
    
    //微信关键词入库
    public function weixinReplySave(){
            if(IS_POST){
                    $PublicKeywordsModel = D('PublicKeywords');
                    $id =   I('post.id',0,'intval');
                    $data   =   array();
                    if($id  ==  0){
                            $data     = $PublicKeywordsModel->create(I('post.'), 1);
                            if ( empty($data) ) {
                                $this->error($PublicKeywordsModel->getError());
                            } else {
                                if(!empty($data['reply_text'])){
                                    $data['reply_text'] = base64_encode($data['reply_text']);
                                }
                                if ( $PublicKeywordsModel->data($data)->add() >= 0 ) {
                                    $this->success('保存成功！');
                                } else {
                                    $this->error('保存失败！');
                                }
                            }
                    }  else {
                            // 如果父级ID大于等于3 则不能再继续保存
                            $data     = $PublicKeywordsModel->create(I('post.'), 2);
                            if ( empty($data) ) {
                                $this->error($PublicKeywordsModel->getError());
                            } else {
                                if(!empty($data['reply_text'])){
                                    $data['reply_text'] = base64_encode($data['reply_text']);
                                }
                                if ( $PublicKeywordsModel->where(array('id'=>$id))->data($data)->save() >= 0 ) {
                                    $this->success('保存成功！',U('weixinReply'));
                                } else {
                                    $this->error('保存失败！');
                                }
                            }
                    }
            }
    }
    
    //关键词回复删除
    public function weixinReplyDel(){
            $id = I('get.id', '', 'int');

            if ( empty($id) ) {
                $this->error('ID 参数丢失！');
            }

            $public_keywords = M('public_keywords');
            // 删除本品牌与本品牌下面的子品牌
            if ( $public_keywords->where(array('id'=>$id))->delete() ) {
                $this->success('删除成功！');
            } else {
                $this->error('删除失败！');
            }
        
    }
    
    
    //自动回复
    public function weixinAutoReply(){
            if(IS_POST){
                $PublicMaterialText = D('PublicMaterialText');
                $id =   I('post.id',0);
                if($id  ==  0){
                        $data     = $PublicMaterialText->create(I('post.'), 1);
                        if (empty($data)) {
                            $this->error($PublicMaterialText->getError());
                        } else {
                            // 如果父级ID大于等于3 则不能再继续保存
                            $data['title']  =   '自动回复';
                            if(!empty($data['desc'])){
                                $data['desc']   = base64_encode($data['desc']);
                            }
                            if ($PublicMaterialText->data($data)->add()) {
                                $this->success('保存成功！', U('System/weixinAutoReply'));
                            } else {
                                $this->error('保存失败！');
                            }
                        }
                }  else {
                        $data     = $PublicMaterialText->create(I('post.'), 2);
                        if (empty($data)) {
                            $this->error($PublicMaterialText->getError());
                        } else {
                            // 如果父级ID大于等于3 则不能再继续保存
                            if(!empty($data['desc'])){
                                $data['desc']   = base64_encode($data['desc']);
                            }
                            if ($PublicMaterialText->data($data)->save()) {
                                $this->success('保存成功！', U('System/weixinAutoReply'));
                            } else {
                                $this->error('保存失败！');
                            }
                        }
                }
            }  else {
                
                $uid    = 8;
                $where  =   array();
                $where['uid']       =   $uid;
                $where['title']     =   '自动回复';
                $weixin_reply =   M('public_material_text')->where($where)->find();
                if(!empty($weixin_reply['desc'])){
                    $weixin_reply['desc']   = base64_decode($weixin_reply['desc']);
                }
                
                $this->assign('weixin_reply', $weixin_reply);
                $this->display('weixin_auto_reply');
            }
    }
    
    
    //关注回复语
    public function weixinSubscribe(){
            if(IS_POST){
                $PublicMaterialText = D('PublicMaterialText');
                $id =   I('post.id',0);
                if($id  ==  0){
                        $data     = $PublicMaterialText->create(I('post.'), 1);
                        if (empty($data)) {
                            $this->error($PublicMaterialText->getError());
                        } else {
                            // 如果父级ID大于等于3 则不能再继续保存
                            $data['title']  =   '首次关注';
                            if(!empty($data['desc'])){
                                $data['desc']   = base64_encode($data['desc']);
                            }
                            if ($PublicMaterialText->data($data)->add()) {
                                $this->success('保存成功！', U('System/weixinSubscribe'));
                            } else {
                                $this->error('保存失败！');
                            }
                        }
                }  else {
                        $data     = $PublicMaterialText->create(I('post.'), 2);
                        if (empty($data)) {
                            $this->error($PublicMaterialText->getError());
                        } else {
                            // 如果父级ID大于等于3 则不能再继续保存
                            if(!empty($data['desc'])){
                                $data['desc']   = base64_encode($data['desc']);
                            }
                            if ($PublicMaterialText->data($data)->save()) {
                                $this->success('保存成功！', U('System/weixinSubscribe'));
                            } else {
                                $this->error('保存失败！');
                            }
                        }
                }
            }  else {
                
                $uid    = 8;
                $where  =   array();
                $where['uid']       =   $uid;
                $where['title']     =   '首次关注';
                $weixin_reply =   M('public_material_text')->where($where)->find();
                if(!empty($weixin_reply['desc'])){
                    $weixin_reply['desc']   = base64_decode($weixin_reply['desc']);
                }
                
                $this->assign('weixin_reply', $weixin_reply);
                $this->display('weixin_subscribe');
            }
    }
    
    
    /*
     * 自定义菜单
     */
    public function weixinMenu() {
            
            //公众帐号信息
            $agentId    = 8;
            $PublicAccountInfo   =   M('public_account')->where(array('redid'=>$agentId))->find();
            $PublicAccountInfo['menu']	= !empty($PublicAccountInfo['menu']) ? json_encode(unserialize(base64_decode($PublicAccountInfo['menu']))) : '';
            $this->assign('info',$PublicAccountInfo);
            //应用分类
            $PublicAppCatModel		= M('PublicApplicationCategory');
            $PublicAppCatList		= $PublicAppCatModel->where(array('status'=>1))->field('id,cate_name')->select();
            $AppCatList				= array();
            if (!empty($PublicAppCatList))
            {
                    foreach ($PublicAppCatList as $k=>$v)
                    {
                            $AppCatList[$v['id']]	= $v['cate_name'];
                    }
                    unset($PublicAppCatList);
            }
            $this->assign('AppCatList',$AppCatList);
            //应用列表
            $PublicAppModel			= M('PublicApplication');
            $PublicAppList			= $PublicAppModel->where(array('status'=>1))->field('id,title,mold,template_url')->select();
            $AppList				= array();
            if (!empty($PublicAppList))
            {
                    foreach($PublicAppList AS $value){
                            $AppList[$AppCatList[$value['mold']]][]	= $value;
                            $AppId[]								= $value['id'];
                    }
            }
            $this->assign('AppList',$AppList);
            $this->display('weixin_create_menu');
    }
    
    
    public function saveMenu()  {
            $id						= I('post.id');
            //公众帐号信息
            $PublicAccountModel		= D('PublicAccount');
            $Fields					= array('id','redid');
            $PublicAccountInfo		= $PublicAccountModel->field($Fields)->find($id);
            if(empty($PublicAccountInfo)) $this->error('接口不存在!');
            if ($PublicAccountInfo['redid'] != 8) $this->error('权限不足!');
            $menu					= I('post.menu');
            $menu					= !empty($menu) ? json_decode(stripslashes($menu),true) : $this->error('保存失败：菜单数据不能为空!');
            //过滤空数据
            foreach($menu as $key=>$value){
                    if(empty($value['name'])){
                            unset($menu['menu'][$key]);
                    }else{
                            if(!empty($menu['list'])){
                                    foreach($value['list'] as $k=>$v){
                                            if(empty($v['name'])){
                                                    unset($menu['menu'][$key]['list'][$k]);
                                            }
                                    }
                            }elseif(empty($value['list'])){
                                    unset($menu['menu'][$key]['list']);
                            }
                    }
            }
            $updata['id']		= $PublicAccountInfo['id'];
            $updata['menu']		=!empty($menu) ? base64_encode(serialize($menu)) : '';
            $PublicAccountModel->save($updata);
            $this->success('保存成功!',U('weixinMenu'));
    }
}