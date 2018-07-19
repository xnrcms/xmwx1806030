<?php
// +------------------------------------------------------------------------------------------------------------------------------------------------------
// | 支付宝支付相关接口助手
// +------------------------------------------------------------------------------------------------------------------------------------------------------
// | Copyright (c) 2017 杭州新苗网络科技有限公司
// +------------------------------------------------------------------------------------------------------------------------------------------------------
// | Date: 2017/11/13 10:10
// +------------------------------------------------------------------------------------------------------------------------------------------------------
// | Author: 田立龙 <longvip199405@163.com>
// +------------------------------------------------------------------------------------------------------------------------------------------------------
// | 退款失败编码说明:    ACQ.SYSTEM_ERROR	                系统错误	            请使用相同的参数再次调用
// |                    ACQ.INVALID_PARAMETER	            参数无效	            请求参数有错，重新检查请求后，再调用退款
// |                    ACQ.SELLER_BALANCE_NOT_ENOUGH	    卖家余额不足	        商户支付宝账户充值后重新发起退款即可
// |                    ACQ.REFUND_AMT_NOT_EQUAL_TOTAL	    退款金额超限	        检查退款金额是否正确，重新修改请求后，重新发起退款
// |                    ACQ.REASON_TRADE_BEEN_FREEZEN	    请求退款的交易被冻结	联系支付宝小二，确认该笔交易的具体情况
// |                    ACQ.TRADE_NOT_EXIST	                交易不存在           检查请求中的交易号和商户订单号是否正确，确认后重新发起
// |                    ACQ.TRADE_HAS_FINISHED	            交易已完结	        该交易已完结，不允许进行退款，确认请求的退款的交易信息是否正确
// |                    ACQ.TRADE_STATUS_ERROR	            交易状态非法	        查询交易，确认交易是否已经付款
// |                    ACQ.DISCORDANT_REPEAT_REQUEST	    不一致的请求	        检查该退款号是否已退过款或更换退款号重新发起请求
// |                    ACQ.REASON_TRADE_REFUND_FEE_ERR	    退款金额无效	        检查退款请求的金额是否正确
// |                    ACQ.TRADE_NOT_ALLOW_REFUND	        当前交易不允许退款	检查当前交易的状态是否为交易成功状态以及签约的退款属性是否允许退款，确认后，重新发起请求
// +------------------------------------------------------------------------------------------------------------------------------------------------------
// | 退款查询失败编码说明:     ACQ.SYSTEM_ERROR	                    系统错误	            重新发起请求
// |                        ACQ.INVALID_PARAMETER	                参数无效	            检查请求参数，修改后重新发起请求
// |                        TRADE_NOT_EXIST	                        查询退款的交易不存在	确认交易号是否为正确的支付宝交易号，修改后重新查询
// +------------------------------------------------------------------------------------------------------------------------------------------------------
// | 转账提现失败编码说明： INVALID_PARAMETER	            参数有误。	请检查入参：必填参数是否为空，长度超出规定限制长度 或 是否不符合格式。
// |                     SYSTEM_ERROR	                系统繁忙	可能发生了网络或者系统异常，导致无法判定准确的转账结果。此时，商户不能直接当做转账成功或者失败处理，可以考虑采用相同的out_biz_no重发请求，或者通过调用“(alipay.fund.trans.order.query)”来查询该笔转账订单的最终状态。
// |                     PERMIT_CHECK_PERM_LIMITED	    根据监管部门的要求，请补全您的身份信息解除限制	根据监管部门的要求，请补全您的身份信息解除限制
// |                     PAYCARD_UNABLE_PAYMENT	        付款账户余额支付功能不可用	请登录支付宝站内或手机客户端开启付款账户余额支付功能。
// |                     PAYEE_NOT_EXIST	            收款账号不存在	请检查payee_account, payee_type是否匹配，如匹配，请检查payee_account是否存在。如果传了payee_real_name，请检查payee_real_name是否与payee_account匹配。
// |                     PAYER_DATA_INCOMPLETE	        根据监管部门的要求，需要付款用户补充身份信息才能继续操作	请付款方登录支付宝站内或手机客户端补充身份信息
// |                     PERM_AML_NOT_REALNAME_REV	    根据监管部门的要求，需要收款用户补充身份信息才能继续操作	请联系收款方登录支付宝站内或手机客户端补充身份信息
// |                     PERM_AML_NOT_REALNAME_REV	    根据监管部门的要求，需要收款用户补充身份信息才能继续操作	请联系收款方登录支付宝站内或手机客户端补充身份信息
// |                     PAYER_STATUS_ERROR	            付款账号状态异常	请检查付款方是否进行了自助挂失，如果无，请联系支付宝客服检查付款用户状态是否正常。
// |                     PAYER_STATUS_ERROR	            付款方用户状态不正常	请检查付款方是否进行了自助挂失，如果无，请联系支付宝客服检查用户状态是否正常。
// |                     PAYEE_USER_INFO_ERROR	        支付宝账号和姓名不匹配，请确认姓名是否正确	请联系收款方确认收款用户姓名正确性。
// |                     PAYER_USER_INFO_ERROR	        付款用户姓名或其它信息不一致	请检查接口传递的付款方用户姓名正确性。
// |                     PAYER_DATA_INCOMPLETE	        根据监管部门的要求，需要付款用户补充身份信息才能继续操作	根据监管部门的要求，需要付款用户登录支付宝站内或手机客户端补充身份信息才能继续操作
// |                     PAYER_BALANCE_NOT_ENOUGH	    付款方余额不足	支付时间点付款方余额不足，请保持付款账户余额充足。
// |                     PAYMENT_INFO_INCONSISTENCY	    两次请求商户单号一样，但是参数不一致	如果想重试前一次的请求，请用原参数重试，如果重新发送，请更换单号。
// |                     CERT_MISS_TRANS_LIMIT	        您的付款金额已达单笔1万元或月累计5万元，根据监管部门的要求，需要付款用户补充身份信息才能继续操作	您的付款金额已达单笔1万元或月累计5万元，根据监管部门的要求，需要付款用户登录支付宝站内或手机客户端补充身份信息才能继续操作。
// |                     CERT_MISS_ACC_LIMIT	        您连续10天余额账户的资金都超过5000元，根据监管部门的要求，需要付款用户补充身份信息才能继续操作	您连续10天余额账户的资金都超过5000元，根据监管部门的要求，需要付款用户登录支付宝站内或手机客户端补充身份信息才能继续操作。
// |                     PAYEE_ACC_OCUPIED	            该手机号对应多个支付宝账户，请传入收款方姓名确定正确的收款账号	如果未传入payee_account_name，请传递payee_account_name；如果传递了payee_account_name，是因为收款登录号对应多个账户且账户名相同，请联系收款方更换登录号。
// |                     MEMO_REQUIRED_IN_TRANSFER_ERROR	    根据监管部门的要求，单笔转账金额达到50000元时，需要填写付款理由	请检查remark是否为空。
// |                     PERMIT_NON_BANK_LIMIT_PAYEE	根据监管部门的要求，对方未完善身份信息或未开立余额账户，无法收款	请联系收款方登录支付宝站内或手机客户端完善身份信息后，重试。
// |                     PERMIT_PAYER_LOWEST_FORBIDDEN	根据监管部门要求，付款方身份信息完整程度较低，余额支付额度受限	请付款方登录支付宝站内或手机客户端检查自己的支付额度，建议付款方尽快登录支付宝站内善身份信息提升额度。
// |                     PERMIT_PAYER_FORBIDDEN	        根据监管部门要求，付款方余额支付额度受限	请付款方登录支付宝站内或手机客户端检查自己的支付额度。
// |                     PERMIT_CHECK_PERM_IDENTITY_THEFT	    您的账户存在身份冒用风险，请进行身份核实解除限制	您的账户存在身份冒用风险，请进行身份核实解除限制
// |                     REMARK_HAS_SENSITIVE_WORD	    转账备注包含敏感词，请修改备注文案后重试	转账备注包含敏感词，请修改备注文案后重试
// |                     ACCOUNT_NOT_EXIST	            根据监管部门的要求，请补全你的身份信息，开立余额账户	请付款方登录支付宝站内或手机客户端补全身份信息。
// |                     PAYER_CERT_EXPIRED	            根据监管部门的要求，需要付款用户更新身份信息才能继续操作	根据监管部门的要求，需要付款用户登录支付宝站内或手机客户端更新身份信息才能继续操作。
// |                     PERMIT_NON_BANK_LIMIT_PAYEE	    根据监管部门的要求，对方未完善身份信息或未开立余额账户，无法收款	请联系收款方登录支付宝站内或手机客户端完善身份信息后，重试。
// |                     EXCEED_LIMIT_PERSONAL_SM_AMOUNT	    转账给个人支付宝账户单笔最多5万元	转账给个人支付宝账户单笔最多5万元。
// |                     EXCEED_LIMIT_ENT_SM_AMOUNT	    转账给企业支付宝账户单笔最多10万元	转账给企业支付宝账户单笔最多10万元。
// |                     EXCEED_LIMIT_SM_MIN_AMOUNT	    单笔最低转账金额0.1元	请修改转账金额。
// |                     EXCEED_LIMIT_DM_MAX_AMOUNT	    单日最多可转100万元	单日最多可转100万元。
// |                     EXCEED_LIMIT_UNRN_DM_AMOUNT	    收款账户未实名，单日最多可收款1000元	收款账户未实名，单日最多可收款1000元。
// +------------------------------------------------------------------------------------------------------------------------------------------------------
// | 转账提现查询失败编码说明：ORDER_NOT_EXIST	转账订单不存在	转账订单不存在的原因,可能是转账还在处理中,也可能是转账处理失败,导致转账订单没有落地。商户首先需要检查该订单号是否确实是自己发起的, 如果确认是自己发起的转账订单,请不要直接当作转账失败处理,请隔几分钟再尝 试查询,或者通过相同的 out_biz_no 再次发起转账。如果误把还在转账处理中的订单直接当转账失败处理,商户自行承担因此而产生的所有损失。
// |                        NO_ORDER_PERMISSION	商家没有该笔订单的操作权限	请重新检查一下查询条件是否正确。商户只允许查询自己发起的转账订单,如果查询的转账订单不属于该商户就会报该错误。
// |                        INVALID_PARAMETER	参数有误。	请检查请求参数的长度正确性和合法性，out_biz_no与order_id不能同时为空
// |                        ILLEGAL_ACCOUNT_STATUS_EXCEPTION	账户状态异常	请检查一下账户状态是否正常，如果账户不正常请联系支付宝小二。联系方式：https://support.open.alipay.com/alipay/support/index.htm
// |                        SYSTEM_ERROR	系统繁忙	支付宝系统繁忙或者处理超时，请稍后重试。
// +------------------------------------------------------------------------------------------------------------------------------------------------------
// | 注意: 使用退款功能需要APP三方授权  具体操作查看 https://docs.open.alipay.com/common/105193
// +------------------------------------------------------------------------------------------------------------------------------------------------------
//
namespace Api\Helper ;
use Vendor\Aop ;
use Vendor\Aop\request ;
class AlipayHelper {

    //有关支付字段
    public $order_sn ;                  //订单编号
    public $subject ;                   //商品的标题/交易标题/订单标题/订单关键字等。
    public $body ;                      //对一笔交易的具体描述信息。如果是多种商品，请将商品描述字符串累加传给body。
    public $fee ;                       //订单总金额，单位为元，精确到小数点后两位，取值范围[0.01,100000000]
    public $notify_url ;
    public $goods_type ;                //商品主类型：0—虚拟类商品，1—实物类商品  注：虚拟类商品不支持使用花呗渠道

    //有关退款字段
    public $trade_no ;               //支付宝交易号，和商户订单号不能同时为空
    public $out_trade_no ;           //订单支付时传入的商户订单号,和支付宝交易号不能同时为空。
    public $out_request_no ;         //标识一次退款请求，同一笔交易多次退款需要保证唯一，如需部分退款，则此参数必传。
    public $refund_amount ;          //需要退款的金额，该金额不能大于订单金额,单位为元，支持两位小数,取值范围[0.01,100000000]
    public $refund_reason ;          //退款的原因说明

    //有关转账提现字段
    public $out_biz_no ;                //必选 商户转账唯一订单号。发起转账来源方定义的转账单据ID，用于将转账回执通知给来源方。不同来源方给出的ID可以重复，同一个来源方必须保证其ID的唯一性。只支持半角英文、数字，及“-”、“_”。
    public $payee_type ;                //必选 收款方账户类型。可取值：1、ALIPAY_USERID：支付宝账号对应的支付宝唯一用户号。以2088开头的16位纯数字组成。2、ALIPAY_LOGONID：支付宝登录号，支持邮箱和手机号格式。
    public $payee_account ;             //必选 收款方账户。与payee_type配合使用。付款方和收款方不能是同一个账户。
    public $amount ;                    //必选 转账金额，单位：元。只支持2位小数，小数点前最大支持13位，金额必须大于等于0.1元。最大转账金额以实际签约的限额为准。
    public $payer_show_name ;           //可选 付款方姓名（最长支持100个英文/50个汉字）。显示在收款方的账单详情页。如果该字段不传，则默认显示付款方的支付宝认证姓名或单位名称。
    public $payee_real_name ;           //可选 收款方真实姓名（最长支持100个英文/50个汉字）。如果本参数不为空，则会校验该账户在支付宝登记的实名是否与收款方真实姓名一致。
    public $remark ;                    //可选 转账备注（支持200个英文/100个汉字）。当付款方为企业账户，且转账金额达到（大于等于）50000元，remark不能为空。收款方可见，会展示在收款用户的收支详情中。

    public $order_id ;                  //支付宝转账单据号：和商户转账唯一订单号不能同时为空。当和商户转账唯一订单号同时提供时，将用本参数进行查询，忽略商户转账唯一订单号。

    var  $aop ;
    public function __construct()
    {
        //支付宝客户端
        Vendor('AlipayNew.AopClient') ;

        //支付宝支付
        Vendor('AlipayNew.request.AlipayTradeAppPayRequest') ;

        //支付宝退款
        Vendor('AlipayNew.request.AlipayTradeRefundRequest') ;

        //支付宝退款查询
        Vendor('AlipayNew.request.AlipayTradeFastpayRefundQueryRequest') ;

        //支付宝转账提现
        Vendor('AlipayNew.request.AlipayFundTransToaccountTransferRequest') ;

        //支付宝转账提现进度查询
        Vendor('AlipayNew.request.AlipayFundTransOrderQueryRequest') ;


        $this->notify_url = C('NOTICE_URL') ;
        $this->aop    =    new Aop\AopClient();
        $this->aop->gatewayUrl              = C('ALIPAY_GATEWAYURL');
        $this->aop->appId                   = C('APPID');
        $this->aop->rsaPrivateKey           = C('RSAPRIVATEKEY');//私有密钥
        $this->aop->format                  = "json";
        $this->aop->charset                 = "UTF-8";
        $this->aop->signType                = "RSA2";
        $this->aop->alipayrsaPublicKey      = C('RSAPUBLICKEY');//共有密钥

    }


    /**
    * @Author :田立龙<longvip199405@163.com>
    * @date：2017/11/11 18:05
    * @description：APP支付
    */
    public function AppPay(){

        $order = json_encode([
            'body'                  => (string)$this-> body ,
            'subject'               => (string)$this-> subject,
            'out_trade_no'          => (string)$this-> order_sn,//此订单号为商户唯一订单号
            'total_amount'          => (string)$this-> fee,//保留两位小数
            'product_code'          => 'QUICK_MSECURITY_PAY',
            'timeout_express'       => '30m'
        ]);


        $request = new request\AlipayTradeAppPayRequest();

        $request->setNotifyUrl($this->notify_url);
        $request->setBizContent($order);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $this->aop->sdkExecute($request);
        return $response ;

    }

    /**
    * @Author :田立龙<longvip199405@163.com>
    * @date：2017/11/13 09:53
    * @description：发起退款
    */
    public function refund(){
        $refund = json_encode([
            'out_trade_no'          => (string) $this->out_trade_no ,
            'trade_no'              => (string) $this->trade_no ,
            'refund_amount'         => (string) $this->refund_amount ,
            'refund_reason'         => (string) $this->refund_reason ,
            'out_request_no'         => (string) $this->out_request_no ,
        ]) ;

        $request = new request\AlipayTradeRefundRequest() ;
        $request->setBizContent($refund) ;

        $result = $this->aop->execute ( $request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";

        $resultCode = $result->$responseNode->code;
        $resultMsg = $result->$responseNode->msg;

        if(!empty($resultCode)&&$resultCode == 10000){
            return array('Code' => 0 , 'Msg' => '发起退款成功' ,'Data' =>$result->$responseNode) ;
        } else {
            return array('Code' => $resultCode , 'Msg' => $resultMsg , 'Data' => $responseNode) ;
        }
    }

    /**
    * @Author :田立龙<longvip199405@163.com>
    * @date：2017/11/13 10:23
    * @description：退款进度查询
    */

    public function refundQuery(){

        $query = json_encode([
            'out_trade_no'          => (string) $this->out_trade_no ,
            'trade_no'              => (string) $this->trade_no ,
            'out_request_no'         => (string) $this->out_request_no ,
        ]) ;

        $request = new request\AlipayTradeFastpayRefundQueryRequest() ;

        $result = $this->aop->execute ( $query);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        $resultMsg = $result->$responseNode->msg;

        if(!empty($resultCode)&&$resultCode == 10000){
            return array('Code' => 0 , 'Msg' => '查询成功' ,'Data' =>$result->$responseNode) ;
        } else {
            return array('Code' => $resultCode , 'Msg' => $resultMsg , 'Data' => $result->$responseNode) ;
        }
    }

    /**
    * @Author :田立龙<longvip199405@163.com>
    * @date：2017/11/13 11:56
    * @description：支付回调校验
    * @return bool [true]-成功 [false]-失败
    */
    public function notice(){
        $data = $_POST ;
        $res = $this->aop->rsaCheckV1($data,null,'RSA2') ;
        if($res === true){
        	echo 'success' ;    //通知阿里服务器已经正确收到回调请求
        	return true ;
        }else{
        	echo 'fail' ;
        	return false ;
        }
    }

    /**
    * @Author :田立龙<longvip199405@163.com>
    * @date：2017/11/28 13:24
    * @description：转账提现接口
    */
    public function transfer(){
        $order = [
            "out_biz_no"        => $this->out_biz_no,
            "payee_type"        => $this->payee_type,
            "payee_account"     => $this->payee_account,
            "amount"            => $this->amount,
            "payer_show_name"   => $this->payer_show_name,
            "payee_real_name"   => $this->payee_real_name,
            "remark"            => $this->remark
        ];

        //校验非必填参  为空时取消
        if(empty($order['payer_show_name'])) unset($order['payer_show_name']) ;
        if(empty($order['payee_real_name'])) unset($order['payee_real_name']) ;
        if(empty($order['remark'])) unset($order['remark']) ;

        $request = new request\AlipayFundTransToaccountTransferRequest() ;


        $request->setBizContent(json_encode($order)) ;

        $result = $this->aop->execute( $request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        $resultMsg = $result->$responseNode->msg;

        if(!empty($resultCode)&&$resultCode == 10000){
            return array('Code' => 0 , 'Msg' => '提现申请成功' ,'Data' =>$result->$responseNode) ;
        }else {
            return array('Code' => $resultCode, 'Msg' => $resultMsg, 'Data' => $result->$responseNode);
        }
    }

    /**
    * @Author :田立龙<longvip199405@163.com>
    * @date：2017/11/28 14:25
    * @description：转账提现进度查询
    */
    public function transferQuery(){
        $order = [
            "out_biz_no"        => $this->out_biz_no,
            "order_id"          => $this->payee_type,
        ];

        //校验非必填参  为空时取消
        if(empty($order['payer_show_name']) && empty($order['payee_real_name'])) return ['Code'=>'11','Msg'=>'out_biz_no和order_id不能同时为空'] ;
        if(empty($order['payer_show_name'])) unset($order['payer_show_name']) ;
        if(empty($order['payee_real_name'])) unset($order['payee_real_name']) ;

        $request = new request\AlipayFundTransOrderQueryRequest() ;

        $request->setBizContent(json_encode($order)) ;

        $result = $this->aop->execute( $request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        $resultMsg = $result->$responseNode->msg;

        if(!empty($resultCode)&&$resultCode == 10000){
            return array('Code' => 0 , 'Msg' => '提现查询成功' ,'Data' =>$result->$responseNode) ;
        }else {
            return array('Code' => $resultCode, 'Msg' => $resultMsg, 'Data' => $result->$responseNode);
        }
    }

}