<?php
namespace Admin\Controller;

/**
 * 后台配置控制器
 */
class DataController extends AdminController {

    /**
     * 商品列表
     * @author xiaoQ
     */
    public function index() {
    	
    	//搜索的所有商家
    	$shop = M('shop')->field('id,shop_name')->select();
    	$this->assign('shop', $shop);
    	
        //获取数据
        $MainTab = 'order';
        
        //检索条件
        $map = array();
        //时间区间检索
        //开始时间
        $time_s			= I('get.create_time_s', '', 'trim');
        if(!empty($time_s)){
        	$map['create_time'][] = array('egt',strtotime($time_s));
        }else{
        	$map['create_time'][] = array('egt',strtotime(date('Y-m-d')));
        }
        //结束时间
        $time_e			= I('get.create_time_e', '', 'trim');
        if(!empty($time_e)){
        	$map['create_time'][] = array('elt',strtotime($time_e)+86400);
        }else{
        	$map['create_time'][] = array('elt',strtotime(date('Y-m-d'))+86400);
        }
       	//状态检索
        //0未支付 1已支付
        $map['pay_status'] = 1;

        //销售额
        $totalMoney = M($MainTab)->where($map)->sum('total_money');
        $this->assign('totalMoney', $totalMoney);
        
        //订单总数
        unset($map['pay_status']);
        $totalOrder = M($MainTab)->where($map)->count();
        $this->assign('totalOrder', $totalOrder);
        
        //新增会员
        unset($map['pay_status']);
        $totalMember = M('user')->where($map)->count();
        $this->assign('totalMember', $totalMember);

        //操作菜单,可以根据需要固定$menuid,$menuid为Menu表中的ID
        $menuid = $this->menuid;
        $SonMenu = $this->getSonMenu($menuid);
        $this->assign('ListTopNav', !empty($SonMenu['TOPMENU']) ? $SonMenu['TOPMENU'] : array());
        $this->assign('ListRightNav', !empty($SonMenu['RIGHTMENU']) ? $SonMenu['RIGHTMENU'] : array());

        //代码扩展
        $this->extends_param .= $this->extends_param . '&cate_id=' . I('get.cate_id');
        //.........
        //代码扩展
        $this->NavTitle = '统计信息';
        $this->assign('SmallNav', array('统计信息', '基础统计'));
        //记录当前列表页的cookie
        if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf'))
            Cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->display();
    }

    /**
     * 图表统计
     */
    public function bar() {
    	
    	//搜索的所有商家
    	$shop = M('shop')->field('id,shop_name')->select();
    	$this->assign('shop', $shop);
    	
    	//获取数据
    	$MainTab = 'order';
    
    	//检索条件
    	$userMap = $map = $ymap = array();
    	//时间区间检索
    	//开始时间
    	$time_s			= I('get.create_time_s', '', 'trim');
    	if(empty($time_s)){
    		$time_s		= date('Y-m-d', strtotime('-30 day'));
    	}
    	//结束时间
    	$time_e			= I('get.create_time_e', '', 'trim');
    	if(empty($time_e)){
    		$time_e		= date('Y-m-d');
    	}
    	//时间
    	$begintime = strtotime($time_s);
    	$endtime = strtotime($time_e);
    	for ($start = $begintime; $start <= $endtime; $start += 24 * 3600) {
    		$date[] = date("Y-m-d", $start);
    	}
    	$jsonDate = json_encode($date);
    	$this->assign('jsonDate', $jsonDate);
    	
    	//状态检索
    	//0未支付 1已支付
       
    	foreach ($date as $key=>$value){
    		$map['create_time'] = array(array('egt',strtotime($value)),array('elt',strtotime($value)+86400));
    		//订单总数
    		$count = M($MainTab)->where($map)->count();
    		$totalOrder[] = intval($count);
    		
    		//新增会员
    		$userMap['create_time'] = $map['create_time'];
    		$count = M('user')->where($userMap)->count();
    		$totalMember[] = intval($count);
    		
    		//金额总数
    		$ymap['create_time'] = $map['create_time'];
    		$ymap['pay_status'] = 1;
    		$count = M($MainTab)->where($ymap)->sum('total_money');
    		$totalMoney[] = floatval($count);
    	}
    	
    	$jsonTotalOrder = json_encode($totalOrder);
    	$this->assign('jsonTotalOrder', $jsonTotalOrder);
    	
    	$jsonTotalMember = json_encode($totalMember);
    	$this->assign('jsonTotalMember', $jsonTotalMember);
    	
    	$jsonTotalMoney = json_encode($totalMoney);
    	$this->assign('jsonTotalMoney', $jsonTotalMoney);
    	
    	/* //订单总数
    	$totalOrder = M($MainTab)->where($map)->count();
    	$this->assign('totalOrder', $totalOrder);
    
    	//金额总数
    	$totalMoney = M($MainTab)->where($map)->sum('total_money');
    	$this->assign('totalMoney', $totalMoney); */
    
    
    	//操作菜单,可以根据需要固定$menuid,$menuid为Menu表中的ID
    	$menuid = $this->menuid;
    	$SonMenu = $this->getSonMenu($menuid);
    	$this->assign('ListTopNav', !empty($SonMenu['TOPMENU']) ? $SonMenu['TOPMENU'] : array());
    	$this->assign('ListRightNav', !empty($SonMenu['RIGHTMENU']) ? $SonMenu['RIGHTMENU'] : array());
    
    	//代码扩展
    	$this->extends_param .= $this->extends_param . '&cate_id=' . I('get.cate_id');
    	//.........
    	//代码扩展
    
    	$this->NavTitle = '统计信息';
        $this->assign('SmallNav', array('统计信息', '统计图表'));
    	//记录当前列表页的cookie
    	if (!strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf'))
    		Cookie('__forward__', $_SERVER['REQUEST_URI']);
    		$this->display();
    }


}

?>