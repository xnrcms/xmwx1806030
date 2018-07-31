<?php
namespace Api\Helper;
/**
 * 支付
 * @author 王远庆
 */
class PayHelper extends BaseHelper{
	
	//初始化接口
	public function apiRun($parame = ''){
		//接口分发
		$Parame		= !empty($parame) ? json_decode($parame,true) : '';
		$ac			= $Parame['ac'];
		$isapi		= $Parame['isapi'];
		if ($isapi === true){
			return !empty($ac) ? $this->$ac($Parame) : array('Code' =>'100009','Msg'=>$this->Lang['100009']);
		}
		return array('Code' =>'100007','Msg'=>$this->Lang['100007']);
	}
		
	//统一支付
	private function pay($Parame){	
		$orderId = $Parame['order_id'];
		$order = M('order')->where(array('id'=>$orderId))->find();
		vendor('Wxpay.lib.WxPay#Api');
		vendor('Wxpay.example.WxPay#Config');
		//统一下单
		$input = new \WxPayUnifiedOrder();
		$input->SetBody("商品支付");
		$input->SetOut_trade_no($order['order_no'].'_'.time());
		$input->SetTotal_fee($order['total_money']*100);
		$input->SetNotify_url('http://'.WEB_DOMAIN.'/api/pay/paySuccess/');
		$input->SetTrade_type("JSAPI");
		$openId = M('user')->where(array('id'=>$order['uid']))->getField('openid');
		$input->SetOpenid($openId);
		$config = new \WxPayConfig();
		$order = \WxPayApi::unifiedOrder($config, $input);
		
		$appId 				= C('GZH.APPID');
		$timeStamp			= NOW_TIME;
		$nonceStr			= randomString(32,7);
		$package			= 'prepay_id='.$order['prepay_id'];
		$signType			= 'MD5';
		$data 				= array();
		$data['appId'] 		= $appId;
		$data['timeStamp'] 	= $timeStamp;
		$data['nonceStr'] 	= $nonceStr;
		$data['package'] 	= $package;
		$data['signType'] 	= $signType;
		ksort($data);
		$stringToBeSigned 	= "";
		$i = 0;
		foreach ($data as $k => $v) {
			if ($i == 0) {
				$stringToBeSigned .= "$k" . "=" . "$v";
			} else {
				$stringToBeSigned .= "&" . "$k" . "=" . "$v";
			}
			$i++;
		}
		$string 			= $stringToBeSigned."&key=".$config->GetKey();
		$sign 				= strtoupper(md5($string));
		$paySign 			= $sign;
		$info = array(
			'appId' 			=> $appId,
			'timeStamp'			=> $timeStamp,
			'nonceStr'			=> $nonceStr,
			'package'			=> $package,
			'signType'			=> $signType,
			'paySign'			=> $paySign,
		);
		return array('Code' => 0 , 'Msg' => '获取成功' ,'Data' => $info) ;
	}
	
	//支付成功回调地址
	private function paySuccess(){
		wechat_success();
	}

	//微信通知.v2
	private function wechat_success(){
		vendor('Wxpay.WxPayPubHelper');
		$notify = new \Notify_pub();
		$xml 	= $GLOBALS['HTTP_RAW_POST_DATA'];
		
		file_put_contents('Runtime/1.txt',var_export($xml, TRUE));
		
		
		$notify->saveData($xml);
		if($notify->checkSign($this->cfg['wx_key']) == FALSE){
			//logit('签名失败：FAIL');
			$notify->setReturnParameter("return_code","FAIL");//返回状态码
			$notify->setReturnParameter("return_msg","签名失败");//返回信息
		}else{
			$notify->setReturnParameter("return_code","SUCCESS");//设置返回码
		}
		$returnXml = $notify->returnXml();
		echo $returnXml;

		$temp = $notify->checkSign($this->cfg['wx_key']);

		//==商户根据实际情况设置相应的处理流程=======
		if($notify->checkSign($this->cfg['wx_key']) == TRUE){
			if ($notify->data["return_code"] == "FAIL") {
				//logit("【通信出错】:\n".$xml."\n");
			}
			elseif($notify->data["result_code"] == "FAIL"){
				//logit("【业务出错】:\n".$xml."\n");
			}
			else{
				//此处应该更新一下订单状态，商户自行增删操作
				$order_sn 	= $notify->data['out_trade_no'];
				//$res		= $this->updateOrder($order_sn,$goodsclass);
				
				$res		= $this->updateOrder($table,$order_sn);
			}
		}
	}

	//阿里通知
	private function alipay_success($table){
		
		Vendor('Alipay.AopClient');
		$aop = new \AopClient;
		$aop->alipayrsaPublicKey = C('RSAPUBLICKEY');
		$flag 			= $aop->rsaCheckV1($_POST, NULL, "RSA2");
		if($flag === true){
			$res		= $this->updateOrder($table,$_POST['out_trade_no'],$_POST['receipt_amount']);
			if($res !== true){
				echo 'fail' ;
			}
		}else{
			echo 'fail' ;
		}
		//file_put_contents('a/1.txt',var_export($flag, TRUE));
	}
	
	//回调处理
	private function updateOrder($table,$order_no,$money=''){
		switch ($table){
			case 'order':
				$this->order_table($order_no,$money);
				break;
			case 'advertisement':
				$this->advertisement_table($order_no,$money);
				break;
			case 'video':
				$this->video_table($order_no,$money);
				break;
		}
	
	}
	
	//福利商城订单
	private function order_table($order_no,$money){
		//file_put_contents('a/1.txt',var_export(array($order_no,$money), TRUE));
		
		//修改订单
		$data 							= array();
		$data['status'] 				= 2;
		$data['pay_status']				= 1;
		$data['pay_time']				= NOW_TIME;
		$res 							= M('order')->where(array('order_no'=>$order_no))->save($data);
		if($res != false){
			//支付成功增加销量
			$oid = M('order')->where(array('order_no'=>$order_no))->getField('id');
			$orderDesc	= M('order_desc')->where(array('oid'=>$oid))->select();
			if(!empty($orderDesc)){
				foreach ($orderDesc as $key=>$value){
					$row 						= array();
					$row['salenum'] 			= array('exp',"salenum+{$value['num']}");
					M('goods')->where(array('id'=>$value['gid']))->save($row);
				}
			}
			return true;
		}
		return false;
	}
	
	//消费让利订单
	private function advertisement_table($order_no,$money){
		//修改订单
		$data 							= array();
		$data['pay_status']				= 1;
		$data['pay_time']				= NOW_TIME;
		$res 							= M('advertisement')->where(array('order_no'=>$order_no))->save($data);
		if($res != false){
			return true;
		}
		return false;
	}
	
	//商家视频订单
	private function video_table($order_no,$money){
		//修改订单
		$data 							= array();
		$data['pay_status']				= 1;
		$data['pay_time']				= NOW_TIME;
		$res 							= M('video')->where(array('order_no'=>$order_no))->save($data);
		if($res != false){
			return true;
		}
		return false;
	}
	
	
	
	
	
	
	/*
	 *	微信支付
	 *	2015-12-22 15:40:16
	 *	@param $trade_no	订单号
	 *	@param $body		商品描述
	 *	@param $attach		商户附带
	 *	@param $fee			总金额
	 *	@param $notify_url	通知地址
	 */
	/* private function wechat_app($trade_no,$body,$attach,$fee,$notify_url){
		//	$body = '2234dfsfsdf' ;dblog($body) ;
		vendor('Wxpay.WxPayPubHelper');
		$unifiedOrder = new \UnifiedOrder_pub();
		$unifiedOrder->setParameter("attach",$attach);
		$unifiedOrder->setParameter("body",$body);
		$unifiedOrder->setParameter("out_trade_no",$trade_no);
		$unifiedOrder->setParameter("total_fee",$fee);
		$unifiedOrder->setParameter("notify_url",$notify_url);
		$unifiedOrder->setParameter("trade_type","APP");
		$order 		= $unifiedOrder->getPrepayId();
		$prepay_id 	= $order['prepay_id'];
		if($prepay_id){
			$temp = array(
					'appid'=>$order['appid'],
					'noncestr'=>$order['nonce_str'],
					'package'=>'Sign=WXPay',
					'partnerid'=>$order['mch_id'],
					'prepayid'=>$order['prepay_id'],
					'timestamp'=>(string)NOW_TIME
			);
			ksort($temp);
			$temp['sign'] = $unifiedOrder->getSign($temp);dblog(array('wxPay'=>$temp)) ;
			return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>array('payInfo'=>$temp, 'trade_no'=>$trade_no));
		}else{
			return array('Code' =>'100705','Msg'=>$this->Lang['100705'],'Data'=>array('payInfoWechat'=>$temp));
		}
	} */
	
}
?>