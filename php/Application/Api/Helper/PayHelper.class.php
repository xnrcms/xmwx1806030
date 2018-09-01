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
		$this->wechat_success();
	}

	//微信通知.v2
	private function wechat_success(){
		vendor('Wxpay.WxPayPubHelper');
		$notify = new \Notify_pub();
		$xml 	= $GLOBALS['HTTP_RAW_POST_DATA'];
		$notify->saveData($xml);



file_put_contents('./Data/2.txt',var_export($notify->data,true));


		if($notify->checkSign($this->cfg['wx_key']) == FALSE){

file_put_contents('./Data/3.txt',1);

			//logit('签名失败：FAIL');
			$notify->setReturnParameter("return_code","FAIL");//返回状态码
			$notify->setReturnParameter("return_msg","签名失败");//返回信息
		}else{
file_put_contents('./Data/4.txt',2);

			$notify->setReturnParameter("return_code","SUCCESS");//设置返回码
		}
		$returnXml = $notify->returnXml();
		echo $returnXml;
		$temp = $notify->checkSign($this->cfg['wx_key']);

file_put_contents('./Data/5.txt',$returnXml);

		//==商户根据实际情况设置相应的处理流程=======
		if($notify->checkSign($this->cfg['wx_key']) == TRUE){


			file_put_contents('./Data/1.txt',var_export($notify));


			if ($notify->data["return_code"] == "FAIL") {
				//logit("【通信出错】:\n".$xml."\n");
			}
			elseif($notify->data["result_code"] == "FAIL"){
				//logit("【业务出错】:\n".$xml."\n");
			}
			else{
				//此处应该更新一下订单状态，商户自行增删操作
				$order_no_arr 	= explode('_', $notify->data['out_trade_no']);
				$order_no		= $order_no_arr[0];
				$data 							= array();
				$data['status'] 				= 2;
				$data['pay_status']				= 1;
				$data['pay_time']				= NOW_TIME;
				$res 							= M('order')->where(array('order_no'=>$order_no))->save($data);
				if($res != false){
					//支付成功增加销量
					$orderInfo = M('order')->where(array('order_no'=>$order_no))->find();
					$oid = $orderInfo['id'];
					$uid = $orderInfo['uid'];
					$totalMoney = $orderInfo['total_money'];
					$orderDesc	= M('order_desc')->where(array('oid'=>$oid))->select();
					if(!empty($orderDesc)){
						foreach ($orderDesc as $key=>$value){
							$row 						= array();
							$row['salenum'] 			= array('exp',"salenum+{$value['num']}");
							M('goods')->where(array('id'=>$value['gid']))->save($row);
						}
					}
					//增加用户消费金额
					$row 						= array();
					$row['consumption_money'] 	= array('exp',"consumption_money+$totalMoney");
					M('user')->where(array('id'=>$uid))->save($row);
				}
			}
		}
	}
}
?>