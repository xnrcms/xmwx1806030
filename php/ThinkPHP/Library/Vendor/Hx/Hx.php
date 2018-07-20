<?php

/**
 * ==================================
 * 	说明：
 * 	1、此类为环信即时通讯云服务端集成插件，主要负责与环信第三方服务器交互，以便正常使用环信第三方通讯云功能
 * 	2、当前只集成了部分功能，若需要其他功能，可在此类基础上再做拓展
 * 	3、成员变量为集成过程中必不可少的参数，可根据环信提供的数据进行更改
 * 	4、服务端集成的所有APi都只有ORG管理员或者APP管理员才有权限调用
 * 	5、App很容易被反编译，安全起见，ORG管理员和APP管理员的用户名和密码要保证只存在于服务器脚本中
 * 
 * 	环信服务端集成
 * 	1、构造函数：获取APP管理员Token
 * 	2、注册IM用户(授权注册)
 * 	3、给IM用户的添加好友
 * 	4、解除IM用户的好友关系
 * 	5、查看好友
 * 	6、发送文本消息
 * 	7、查询离线消息数 获取一个IM用户的离线消息数
 * 	8、获取IM用户单个
 * 	9、获取IM用户[批量]
 * 	10、重置IM用户密码
 * 	11、删除IM用户[单个]
 * 	12、修改用户昵称
 * 	13、curl
 *
 * 	TODO：
 * 	1、文件上传下载，
 * 	2、聊天记录（14天），
 * 	3、好友体系，
 * 	4、群组消息，
 * 	5、聊天室，
 * 	6、昵称头像，
 * 	7、数据平滑迁移，
 * 	8、@功能
 * 	9、输入状态提示
 * 	10、消息撤回
 * 	11、实时位置共享
 * 	
 *  	@author 王远庆
 *           2016.07.20
 * ==================================
 */

namespace Vendor\Hx;

class Hxcall {

    private $app_key = '1108180611146057#shengxin';    //应用标识
    private $client_id = 'YXA6YovM4G06EeiXWAOjVdq0sw';  //终端ID
    private $client_secret = 'YXA62eV4MRnoz41jutGtM0BGje9epD4';  //终端密钥
    private $url = "https://a1.easemob.com/1108180611146057/shengxin"; //环信主接口地址

    /**
     * 构造函数：获取APP管理员Token
     * 说明：基于OAuth 2.0 ，获取Token用于确定与环信通讯的权限
     */

    function __construct() {
        $tokenInfo = M('hx_token')->where(array('id' => 1))->field('id,hx_token,expire_in')->find();

        if (!empty($tokenInfo) && $tokenInfo['expire_in'] >= NOW_TIME) {
            $this->token = $tokenInfo['hx_token'];
        } else {
            $url = $this->url . "/token";
            $data = array('grant_type' => 'client_credentials', 'client_id' => $this->client_id, 'client_secret' => $this->client_secret);
            $rs = json_decode($this->curl($url, $data), true);
            $this->token = $rs['access_token'];

            M('hx_token')->save(array('id' => 1, 'hx_token' => $this->token, 'expire_in' => NOW_TIME + 6 * 24 * 3600));
        }
    }

    /*
     * 注册IM用户(授权注册)
     * 说明：
     * 1，$username 不能为中文，email，uuid，不可含有特殊字符
     * 2，环信ID不区分大小写
     */

    public function hx_register($username, $password, $nickname) {
        $url = $this->url . "/users";
        $data = array(
            'username' => $username,
            'password' => $password,
            'nickname' => $nickname
        );
        $header = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token
        );
        return $this->curl($url, $data, $header, "POST");
    }

    /*
     * 给IM用户的添加好友
     */

    public function hx_contacts($owner_username, $friend_username) {
        $url = $this->url . "/users/${owner_username}/contacts/users/${friend_username}";
        $header = array(
            'Authorization: Bearer ' . $this->token
        );
        return $this->curl($url, "", $header, "POST");
    }

    /*
     * 解除IM用户的好友关系
     */

    public function hx_contacts_delete($owner_username, $friend_username) {
        $url = $this->url . "/users/${owner_username}/contacts/users/${friend_username}";
        $header = array(
            'Authorization: Bearer ' . $this->token
        );
        return $this->curl($url, "", $header, "DELETE");
    }

    /*
     * 查看好友
     */

    public function hx_contacts_user($owner_username) {
        $url = $this->url . "/users/${owner_username}/contacts/users";
        $header = array(
            'Authorization: Bearer ' . $this->token
        );
        return $this->curl($url, "", $header, "GET");
    }

    /* 发送文本消息 */

    public function hx_send($sender, $receiver, $msg) {
        $url = $this->url . "/messages";
        $header = array(
            'Authorization: Bearer ' . $this->token
        );
        $data = array(
            'target_type' => 'users',
            'target' => array('0' => $receiver),
            'msg' => array('type' => "txt", 'msg' => $msg),
            'from' => $sender,
            'ext' => array('attr1' => 'v1', 'attr2' => "v2")
        );
        return $this->curl($url, $data, $header, "POST");
    }

    /* 查询离线消息数 获取一个IM用户的离线消息数 */

    public function hx_msg_count($owner_username) {
        $url = $this->url . "/users/${owner_username}/offline_msg_count";
        $header = array(
            'Authorization: Bearer ' . $this->token
        );
        return $this->curl($url, "", $header, "GET");
    }

    /**
     * 获取IM用户单个
     */
    public function hx_user_info($username) {
        $url = $this->url . "/users/${username}";
        $header = array(
            'Authorization: Bearer ' . $this->token
        );
        return $this->curl($url, "", $header, "GET");
    }

    /*
     * 获取IM用户[批量]
     */

    public function hx_user_infos($limit) {
        $url = $this->url . "/users?${limit}";
        $header = array(
            'Authorization: Bearer ' . $this->token
        );
        return $this->curl($url, "", $header, "GET");
    }

    /*
     * 重置IM用户密码
     */

    public function hx_user_update_password($username, $newpassword) {
        $url = $this->url . "/users/${username}/password";
        $header = array(
            'Authorization: Bearer ' . $this->token
        );
        $data['newpassword'] = $newpassword;
        return $this->curl($url, $data, $header, "PUT");
    }

    /*
     * 删除IM用户[单个]
     */

    public function hx_user_delete($username) {
        $url = $this->url . "/users/${username}";
        $header = array(
            'Authorization: Bearer ' . $this->token
        );
        return $this->curl($url, "", $header, "DELETE");
    }

    /*
     * 修改用户昵称
     */

    public function hx_user_update_nickname($username, $nickname) {
        $url = $this->url . "/users/${username}";
        $header = array(
            'Authorization: Bearer ' . $this->token
        );
        $data['nickname'] = $nickname;
        return $this->curl($url, $data, $header, "PUT");
    }

    /*
     * curl
     */

    private function curl($url, $data, $header = false, $method = "POST") {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $ret = curl_exec($ch);
        return $ret;
    }

    /* --------------------------------------------------------------------------------
     * @author:田立龙<longvip199405@163.com>
     * @date:
     * @description:获取聊天记录
     * 	写入文件读取文件目的是为了分页获取数据
     */


    /*
     * 获取聊天记录 不分页获取
     */

    public function chat_record() {

        // $time = $this -> readPage() ;

        if (!empty($time)) {
            $url = $this->url . '/chatmessages?ql=select+*+where+timestamp>' . $time;
        } else {
            $url = $this->url . '/chatmessages/' . date('YmdH', NOW_TIME - 86400 * 3);
        }
        $header[] = $this->token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result, true);

        // if($result['count']){
        // 	foreach ($result as $k => $v) {
        // 		if($k == 'entities')
        // 		foreach ($v as $ke => $val) {
        // 			$times[$ke] = $val['timestamp'] ;
        // 		}
        // 	}
        // 	$pos=array_search(max($times),$times);
        // 	$this -> writePage($result['count'],$times[$pos]) ;
        // }
        return $result;
    }

    //写入记录
    function writePage($num, $time) {
        M('hx_chat_page')->add(array('nums' => $num, 'create_time' => $time));
    }

    //读取记录
    function readPage() {
        $time = M('hx_chat_page')->order('id DESC')->limit(1)->getField('create_time');
        return $time;
    }

}

?>