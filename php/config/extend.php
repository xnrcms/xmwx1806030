<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 缓存设置
// +----------------------------------------------------------------------

return [
    //数据加密KEY
    'uc_auth_key'   			=> '&17@:iY$0?(twB]kru)46J^!9l;.,Z5oE[bI_QmA',
    //系统缓存过期时间
    'cache_time'				=> 3600*24,
    //管理员ID标识
    'administrator_id'			=> 1,
    //是否需要验证码登录 0不需要,1需要
    'is_verify'			        => 0,
    //接口授权配置
    'api_auth_url'              => '',//'http://api3.com/',
    'api_auth_id'				=> '59c28645d615d7b4eafde3ab8a088e8d',
    'api_auth_key'				=> '316d8c27ed26fdf827e8fe1dc93e0ba8',
    //接口导入配置
    'import_api_url'            => 'http://api3.php.hzxmnet.com',
    'import_api_id'             => '59c28645d615d7b4eafde3ab8a088e8d',
    'import_api_key'            => '316d8c27ed26fdf827e8fe1dc93e0ba8',
    //项目开发必须在本地
    'apidoc_project_id'         =>'1',
    'apidoc_project_url'        =>'http://api3.com/',
    //'apidoc_url'                =>'http://apidoc3.com',
    'apidoc_url'                => 'http://apidoc.php.hzxmnet.com/',
    //站点配置
    'xnrcms_name'               =>'小能人CMS',
    'xnrcms_var'                =>'3.0',
    'is_dev'                    => 1,
    'form_type_list'            => [
        'hidden'        => '隐藏域',
        'number'        => '数字',
        'password'      => '密码',
        'string'        => '字符串',
        'price'         => '价格',
        'textarea'      => '文本域',
        'date'          => '时间(Y-m-d)',
        'time'          => '时间(H:i)',
        'datetime'      => '时间(Y-m-d H:i:s)',
        'bool'          => '布尔',
        'select'        => '枚举',
        'radio'         => '单选',
        'checkbox'      => '多选',
        'image'         => '单图上传',
        'images'        => '多图上传',
        'file'          => '文件上传',
        'editor'        => '富文本编辑器',
        'address'       => '城市选择',
        'bdmap'         => '百度地图',
        'gdmap'         => '高德地图',
        'expand'        => '自定义拓展',
    ],
];
