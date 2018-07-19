<?php

/**
 * 支付控制器
 * 王远庆
 */

namespace Admin\Controller;

use Think\Controller;

class PayController extends Controller {

    public $profit_user = array();
    public $parent_num = 1;

    public function __construct() {
        parent::__construct();

    }

    //吊起支付宝支付
    public function pay(){
        //dblog(array('pay start'));
        $out_trade_no   =   I('get.out_trade_no', '', 'trim');
        if(empty($out_trade_no)){
            $this->error('订单参数错误');
        }
        $orderInfo = M('memberRechargeRecord')->where(array('out_trade_no' => $out_trade_no))->field(true)->find();
        if(empty($orderInfo)){
            $this->error('订单不存在');
        }
        
        //支付宝支付
        $notify_url         =   'http://'.$_SERVER['HTTP_HOST'].U('paySuccess');
        $descs              =   '云鸟物流支付';
        $fee                =   $orderInfo['money']; //支付金额
        $trade_sn           =   $orderInfo['out_trade_no'];//支付单号
        $body               =   '购买积分';//TODO 需要完善描述
        $attach             =   '购买积分';
        $return_url         =   'http://'.$_SERVER['HTTP_HOST'].U('alipay_return');
        
        $res                =   $this->alipay_web($trade_sn,$body,$attach,$fee,$notify_url,$return_url);
        dump($res);die;
    }

    protected function alipay_web($trade_sn,$title,$body,$fee,$notify_url,$return_url){
        header("Content-type:text/html;charset=utf-8");
        include_once(VENDOR_PATH.'/Alipay/alipay.config.php');
        include_once(VENDOR_PATH.'/Alipay/lib/alipay_submit.class.php');
        $service                    = $this->iswap() ? 'alipay.wap.create.direct.pay.by.user' : 'create_direct_pay_by_user';
        $parameter = array(
                    "service"           => $service,
                    "partner"           => trim($alipay_config['partner']),
                    "seller_id"         => $alipay_config['seller_id'],
                    "payment_type"      => '1',
                    "notify_url"        => $notify_url,
                    "return_url"        => $return_url,
                    "out_trade_no"      => $trade_sn,
                    "subject"           => $title,
                    "total_fee"         => $fee,
                    "body"              => $body,
                    "anti_phishing_key" => '',
                    "exter_invoke_ip"   => '',
                    "app_pay"           => "Y",//启用此参数能唤起钱包APP支付宝
                    "_input_charset"    => trim(strtolower($alipay_config['input_charset']))
        );
        $alipaySubmit = new \AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter,"post", "确认");
        echo $html_text;die;
        return $result;
    }

    public function getPaystatus(){
        $order_sn   =   I('post.order_sn','','trim');
        //        dump($order_sn);die;
        if(empty($order_sn)){
            $this->error('订单参数错误');
        }
        $order_info =   M('order_payment')->where(array('pay_order_sn'=>$order_sn))->field('id,status')->find();
        if(!empty($order_info)){
            if($order_info['status'] == 1){

                die(json_encode(array('code'=>0,'msg'=>'支付成功')));
            }
        }
        die(json_encode(array('code'=>1,'msg'=>'未支付')));
    } 


    protected function iswap() {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])){
            return true;
        }
        //此条摘自TPM智能切换模板引擎，适合TPM开发
        if(isset ($_SERVER['HTTP_CLIENT']) &&'PhoneClient'==$_SERVER['HTTP_CLIENT']){
            return true;
        }
        //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset ($_SERVER['HTTP_VIA'])){
            //找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
        }
        //判断手机发送的客户端标志,兼容性有待提高
        if (isset ($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array('nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile');
            //从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        //协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }

    
    
    /*
     * 支付回调
     * DML.
     */
    
    public function paySuccess() {
    	dblog(array('paySuccess start'));
    	$this->alipay_success();
    }
    
    //阿里通知
    protected function alipay_success() {
    	include_once(VENDOR_PATH . '/Alipay/alipay.config.php');
    	include_once(VENDOR_PATH . '/Alipay/lib/alipay_notify.class.php');
    	//计算得出通知验证结果
    	$alipayNotify = new \AlipayNotify($alipay_config);
    	$verify_result = $alipayNotify->verifyNotify();
    	if ($verify_result) {//验证成功
    		//商户订单号
    		$out_trade_no = $_POST['out_trade_no'];
    		//支付宝交易号
    		$trade_no = $_POST['trade_no'];
    		//交易状态
    		$trade_status = $_POST['trade_status'];
    		dblog(array($trade_status));
    		if ($trade_status == 'TRADE_FINISHED') {
    			//交易结束
    		} else if ($trade_status == 'TRADE_SUCCESS') {
    			//交易成功
    			$this->update_order($out_trade_no);
    		}
    		echo "success";  //请不要修改或删除
    	} else {
    		//验证失败
    		echo "fail";
    	}
    }
    
    protected function update_order($out_trade_no) {
    	$orderInfo = M('memberRechargeRecord')->where(array('out_trade_no' => $out_trade_no))->find();
    	if(!empty($orderInfo['id'])){
    		$res = M('memberRechargeRecord')->where(array('out_trade_no' => $out_trade_no))->save(array('pay_status'=>1, 'pay_time'=>NOW_TIME));
    		if(false !== $res){
    			/* $currency = $orderInfo['currency'];
    			$integral = $orderInfo['integral']; */
    			$data = array();
    			$data['total_currency'] 		= array('exp',"total_currency+$orderInfo[currency]");
    			$data['current_currency'] 		= array('exp',"current_currency+$orderInfo[currency]");
    			$data['total_integral'] 		= array('exp',"total_integral+$orderInfo[integral]");
    			$data['current_integral'] 		= array('exp',"current_integral+$orderInfo[integral]");
    			M('member')->where(array('uid'=>$orderInfo['mid']))->save($data); // 根据条件保存修改的数据
    		}
    	}
    }
    
    
    // 同步通知处理
    public function alipay_return() {
    	include_once(VENDOR_PATH . '/Alipay/alipay.config.php');
    	include_once(VENDOR_PATH . '/Alipay/lib/alipay_notify.class.php');
    	//计算得出通知验证结果
    	$alipayNotify = new \AlipayNotify($alipay_config);
    	$verify_result = $alipayNotify->verifyReturn();
    	if($verify_result) {
    		//验证成功
    		//请在这里加上商户的业务逻辑程序代码
    		//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
    		//获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
    		//商户订单号
    		$out_trade_no = $_GET['out_trade_no'];
    		//支付宝交易号
    		$trade_no = $_GET['trade_no'];
    		//订单信息
    		$orderInfo = M('memberRechargeRecord')->where(array('out_trade_no' => $out_trade_no))->find();
    		$mid = $orderInfo['gid'];
    		$money = $orderInfo['money'];
    		//交易状态
    		$trade_status = $_GET['trade_status'];
    		if($trade_status==TRADE_FINISHED || $trade_status==TRADE_SUCCESS) {
    			//$this->update_order($out_trade_no);
    			Header("Location:".'http://'.$_SERVER['HTTP_HOST']);
    		}else{
    			Header("Location:".'http://'.$_SERVER['HTTP_HOST']);
    		}
    	}else{
    		//验证失败
    		//如要调试，请看alipay_notify.php页面的verifyReturn函数
    		echo "验证失败";
    	}
    
    }


}

?>                              