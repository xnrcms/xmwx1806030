<?php

namespace Api\Controller;

use User\Api\UserApi;

/**
 * 后台短信控制器
 * @author 王远庆
 */
class SmsController extends CommonController {

    public function index() {
        $this->ReturnJson();
    }

    /**
     * 短信发送
     */
    public function sendcode() {
        $CheckParam = array(
            array('time', 'Int', 1, $this->Lang['100001'], '100001'),
            array('hash', 'String', 1, $this->Lang['100002'], '100002'),
            array('mobile', 'String', 1, $this->Lang['100045'], '100045'),
            array('checktype', 'Int', 1, $this->Lang['100101'], '100101'),//1注册 2找回密码 3手机短信登录 4解除手机绑定 5手机绑定
        );
        $BackData = $this->CheckData(I('request.'), $CheckParam);
        //手机号校验
        $mobile = safe_replace($BackData['mobile']);
        $_REQUEST['username'] = $mobile;
        if (Mobile_check($mobile, array(1)) == false) {
            $this->ReturnJson(array('Code' => '100035', 'Msg' => $this->Lang['100035']));
        }
        //根据短信类型校验是否发送短信
        $error = '';
        switch ($BackData['checktype']) {
            case 1://注册短信 需要验证手机号是否已经被注册
            case 5://短信绑定
            	$count = M('user')->where(array('phone'=>$mobile,'type'=>1))->count();
            	if($count > 0){
            		$this->ReturnJson(array('Code' => 1, 'Msg' => '该手机已经被注册'));
            	}
            	break;
            case 2:	//密码找回 需要验证手机手否已经注册
          	case 3:	//手机短信登录
          	case 4:	//解除绑定
          		$count = M('user')->where(array('phone'=>$mobile,'type'=>1))->count();
          		if($count < 1){
          			$this->ReturnJson(array('Code' => 1, 'Msg' => '该手机未注册'));
          		}
          		//手机短信验证登录 无需验证手机号是否注册
               	break;
        	case 6:	//绑定第三方
              	break;
            default:
                $this->ReturnJson(array('Code' => '100102', 'Msg' => $this->Lang['100102']));
                break;
        }
        $SmsDb = D('Sms');
        $validity = 60 * 30; //30分钟
        $limitime = 60 * 2; //120秒
        $limitip = 5; //同一个IP最多可以发送5次
        //频率限制，120秒内不允许发送下一条
        $ctime = $SmsDb->where(array('mobile' => $mobile, 'status' => 1))->order('id desc')->limit(1)->getField('create_time');
        if (!empty($ctime) && ($ctime + $limitime) > NOW_TIME) {
            $this->ReturnJson(array('Code' => '100104', 'Msg' => $this->Lang['100104']));
        }
        //IP限制，同一个手机号，同个IP每天只能发送5次
        $ipcont = $SmsDb->where(array('mobile' => $mobile, 'ip' => getip(), 'create_time' => array('elt', strtotime(date('Y-m-d', strtotime('+1 day'))))))->count('ip');
        if ($ipcont >= $limitip) {
            $this->ReturnJson(array('Code' => '100105', 'Msg' => $this->Lang['100105']));
        }
        //生成短信内容
        $sign = '【省鑫】';
        $code = randomString('6', 0);
        $conArr[1] = '用户注册验证码为：' . $code . $sign; //可扩展多个验证码内容格式
        $conArr[2] = '找回密码验证码为：' . $code . $sign;
        $conArr[3] = '登录账号验证码为：' . $code . $sign;
        $conArr[4] = '解除账号绑定验证码为：' . $code . $sign;
        $conArr[5] = '账号绑定验证码为：' . $code . $sign;
        $conArr[6] = '账号绑定微信验证码为：' . $code . $sign;
        $content = $conArr[$BackData['checktype']];
        if (empty($content)) {
            $this->ReturnJson(array('Code' => '100106', 'Msg' => $this->Lang['100106']));
        }
        //短信发送
        $ret = sendcode($mobile, $code);
        $ret = 1;
        if ($ret) {
            $smsdata['mobile'] = $mobile;
            $smsdata['checkcode'] = $code;
            $smsdata['sendtype'] = $BackData['checktype'];
            $smsdata['content'] = $content;
            $smsdata['validity'] = NOW_TIME + $validity;
            D('Sms')->update($smsdata);
            $this->ReturnJson(array('Code' => '0', 'Msg' => '发送成功'));
        } else {
            $this->ReturnJson(array('Code' => '100107', 'Msg' => $this->Lang['100107']));
        }
    }

    /**
     * 单独校验验证码
     * @author 王远庆
     */
    public function checkMobile() {
        $CheckParam = array(
            array('time', 'Int', 1, $this->Lang['100001'], '100001'),
            array('hash', 'String', 1, $this->Lang['100002'], '100002'),
            array('mobile', 'String', 1, $this->Lang['100045'], '100045'),
            array('checktype', 'Int', 1, $this->Lang['100101'], '100101'),
            array('checkcode', 'String', 1, $this->Lang['100108'], '100108'),
        );
        $BackData = $this->CheckData(I('request.'), $CheckParam);
        //手机号校验
        $mobile = safe_replace($BackData['mobile']);
        if (Mobile_check($mobile, array(1)) == false) {
            $this->ReturnJson(array('Code' => '100035', 'Msg' => $this->Lang['100035']));
        }
        //校验短信验证码
        if (!$this->checkcode($mobile, $BackData['checkcode'], $BackData['checktype'])) {
            $this->ReturnJson(array('Code' => '100109', 'Msg' => $this->Lang['100109']));
        }
        $this->ReturnJson(array('Code' => '0', 'Msg' => '校验成功'));
    }

    public function checkcode($mobile, $code, $type) {
        if (empty($mobile) || empty($code))
            return false;
        $map = array();
        $map['mobile'] = $mobile;
        $map['validity'] = array('egt', NOW_TIME);
        $map['checkcode'] = $code;
        $map['status'] = 1;
        $map['sendtype'] = $type;
        $iscont = M('Sms')->where($map)->count('id');
        return $iscont;
    }

    public function delcode($mobile, $code, $type) {
        $map = array();
        $map['mobile'] = $mobile;
        $map['checkcode'] = $code;
        $map['status'] = 1;
        $map['sendtype'] = $type;
        M('Sms')->where($map)->delete();
        return true;
    }

}

?>