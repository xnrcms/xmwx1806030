<?php
/**
 * XNRCMS<562909771@qq.com>
 * ============================================================================
 * 版权所有 2018-2028 杭州新苗科技有限公司，并保留所有权利。
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 */
namespace app\api\controller;

use app\common\controller\Base;

class User extends Base
{
    //接口构造
    public function __construct(){

        parent::__construct();
    }

    /**
     * 数据列表接口头
     * @access public
     * @param  [array] $parame [扩展参数]
     * @return [json]          [接口数据输出]
    */
    public function listData($parame = [])
    {
        //执行接口调用
        return $this->execApi($parame);
    }

    /**
     * 接口数据添加/更新头
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
     */
    public function saveData($parame=[])
    {
        //执行接口调用
        return $this->execApi($parame);
    }

    /**
     * 接口数据详情头
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
     */
    public function detailData($parame=[])
    {
        //执行接口调用
        return $this->execApi($parame);
    }

    /**
     * 接口数据快捷编辑头
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
     */
    public function quickEditData($parame=[])
    {
        //执行接口调用
        return $this->execApi($parame);
    }

    /**
     * 接口数据删除头
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
     */
    public function delData($parame=[])
    {
        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:609ef25eb34f9328b296bf3ba71b8ebd*/
    /**
     * 登录（账号+密码）
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function passwordLogin($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:609ef25eb34f9328b296bf3ba71b8ebd*/

    /*api:38ed8d3588e7b824c58f55ffb0d70bd5*/
    /**
     * 用户详情
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function userDetail($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:38ed8d3588e7b824c58f55ffb0d70bd5*/

    /*api:0b5f2683a378793e00e0d97ea79fe6af*/
    /**
     * 用户注册（账号+密码）
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function usernameRegister($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:0b5f2683a378793e00e0d97ea79fe6af*/

    /*api:4bcc00c182dc71fa6c778dc1dd4d36c6*/
    /**
     * 用户资料快捷编辑
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function quickEditUserDetailData($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:4bcc00c182dc71fa6c778dc1dd4d36c6*/

    /*api:ffe672f3176d54cf499926f98488a54d*/
    /**
     * 用户资料更新
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function saveUserDetailData($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:ffe672f3176d54cf499926f98488a54d*/

    /*接口扩展*/
}