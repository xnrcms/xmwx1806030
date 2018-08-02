<?php
// XNRCMS常量定义
const XNRCMS_VERSION    = '1.0';
const XNRCMS_ADDON_PATH = './Addons/';
/**
 * 系统公共库文件
 * 主要定义系统公共函数库
 */
function safe_replace($string) {
	if(is_array($string)){
		$string=implode('，',$string);
		$string=htmlspecialchars(str_shuffle($string));
	} else{
		$string=htmlspecialchars($string);
	}
	$string = str_replace('%20','',$string);
	$string = str_replace('%27','',$string);
	$string = str_replace('%2527','',$string);
	$string = str_replace('*','',$string);
	$string = str_replace('"','&quot;',$string);
	$string = str_replace("'",'',$string);
	$string = str_replace('"','',$string);
	$string = str_replace(';','',$string);
	$string = str_replace('<','&lt;',$string);
	$string = str_replace('>','&gt;',$string);
	$string = str_replace("{",'',$string);
	$string = str_replace('}','',$string);
	return $string;
}
//距离计算
function getDistanceBetweenPointsNew($latitude1, $longitude1, $latitude2, $longitude2)
{
	$theta = $longitude1 - $longitude2;
	$miles = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
	$miles = acos($miles);
	$miles = rad2deg($miles);
	$miles = $miles * 60 * 1.1515;
	$feet = $miles * 5280;
	$yards = $feet / 3;
	$kilometers = $miles * 1.609344;
	$meters = $kilometers * 1000;
	return compact('miles','feet','yards','kilometers','meters');
}
/**
 *<br> 转换  \n\t
 */
function br2nl($text){
	return preg_replace('/<br\\s*?\/??>/i','',$text);
}

/**
 * 检测用户是否登录
 * @return integer 0-未登录，大于0-当前登录用户ID
 */
function is_login(){
	$user = session('user_auth');
	if (empty($user)) {
		return 0;
	} else {
		return session('user_auth_sign') == data_auth_sign($user) ? $user['uid'] : 0;
	}
}

/**
 * 检测当前用户是否为管理员
 * @return boolean true-管理员，false-非管理员
 */
function is_administrator($uid = null){
	$uid = is_null($uid) ? is_login() : $uid;
	return $uid && (intval($uid) === C('USER_ADMINISTRATOR'));
}

/**
 * 字符串转换为数组，主要用于把分隔符调整到第二个参数
 * @param  string $str  要分割的字符串
 * @param  string $glue 分割符
 * @return array
 */
function str2arr($str, $glue = ','){
	$str	= trim($str,$glue);
	return explode($glue, $str);
}

/**
 * 数组转换为字符串，主要用于把分隔符调整到第二个参数
 * @param  array  $arr  要连接的数组
 * @param  string $glue 分割符
 * @return string
 */
function arr2str($arr, $glue = ','){
	$str	= $glue.implode($glue, $arr).$glue;
	return $str;
}

/**
 * 字符串截取，支持中文和其他编码
 * @static
 * @access public
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * @return string
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
	if(function_exists("mb_substr"))
	$slice = mb_substr($str, $start, $length, $charset);
	elseif(function_exists('iconv_substr')) {
		$slice = iconv_substr($str,$start,$length,$charset);
		if(false === $slice) {
			$slice = '';
		}
	}else{
		$re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
		preg_match_all($re[$charset], $str, $match);
		$slice = join("",array_slice($match[0], $start, $length));
	}
	return $suffix ? $slice.'...' : $slice;
}

/**
 * 系统加密方法
 * @param string $data 要加密的字符串
 * @param string $key  加密密钥
 * @param int $expire  过期时间 单位 秒
 * @return string
 */
function think_encrypt($data, $key = '', $expire = 0) {
	$key  = md5(empty($key) ? C('DATA_AUTH_KEY') : $key);
	$data = base64_encode($data);
	$x    = 0;
	$len  = strlen($data);
	$l    = strlen($key);
	$char = '';

	for ($i = 0; $i < $len; $i++) {
		if ($x == $l) $x = 0;
		$char .= substr($key, $x, 1);
		$x++;
	}

	$str = sprintf('%010d', $expire ? $expire + time():0);

	for ($i = 0; $i < $len; $i++) {
		$str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1)))%256);
	}
	return str_replace(array('+','/','='),array('-','_',''),base64_encode($str));
}


function fileUpload($savePath, $callable) {
    if ( !file_exists($savePath) ) {
        mkdir($savePath, 0700, true);
    }

    $upload            = new \Think\Upload();
    $upload->maxSize   = 3145728 ;
    $upload->exts      = array('jpg', 'gif', 'png', 'jpeg','pem');
    $upload->rootPath  = $savePath;
    $info              = $upload->upload();
    if ( !$info ) {
        echo $upload->getError();
    } elseif ( is_callable($callable) ) {
        $keys = array_keys($info);
        $key  = $keys[0];
        $one  = $info[$key];
        $one['filePath'] = $savePath . $one['savepath'] . $one['savename'];
        $callable($one);
    }
}

/**
 * 系统解密方法
 * @param  string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
 * @param  string $key  加密密钥
 * @return string
 */
function think_decrypt($data, $key = ''){
	$key    = md5(empty($key) ? C('DATA_AUTH_KEY') : $key);
	$data   = str_replace(array('-','_'),array('+','/'),$data);
	$mod4   = strlen($data) % 4;
	if ($mod4) {
		$data .= substr('====', $mod4);
	}
	$data   = base64_decode($data);
	$expire = substr($data,0,10);
	$data   = substr($data,10);

	if($expire > 0 && $expire < time()) {
		return '';
	}
	$x      = 0;
	$len    = strlen($data);
	$l      = strlen($key);
	$char   = $str = '';

	for ($i = 0; $i < $len; $i++) {
		if ($x == $l) $x = 0;
		$char .= substr($key, $x, 1);
		$x++;
	}

	for ($i = 0; $i < $len; $i++) {
		if (ord(substr($data, $i, 1))<ord(substr($char, $i, 1))) {
			$str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
		}else{
			$str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
		}
	}
	return base64_decode($str);
}

/**
 * 数据签名认证
 * @param  array  $data 被认证的数据
 * @return string       签名
 */
function data_auth_sign($data) {
	//数据类型检测
	if(!is_array($data)){
		$data = (array)$data;
	}
	ksort($data); //排序
	$code = http_build_query($data); //url编码并生成query字符串
	$sign = sha1($code); //生成签名
	return $sign;
}

/**
 * 对查询结果集进行排序
 * @access public
 * @param array $list 查询结果
 * @param string $field 排序的字段名
 * @param array $sortby 排序类型
 * asc正向排序 desc逆向排序 nat自然排序
 * @return array
 */
function list_sort_by($list,$field, $sortby='asc') {
	if(is_array($list)){
		$refer = $resultSet = array();
		foreach ($list as $i => $data)
		$refer[$i] = &$data[$field];
		switch ($sortby) {
			case 'asc': // 正向排序
				asort($refer);
				break;
			case 'desc':// 逆向排序
				arsort($refer);
				break;
			case 'nat': // 自然排序
				natcasesort($refer);
				break;
		}
		foreach ( $refer as $key=> $val)
		$resultSet[] = &$list[$key];
		return $resultSet;
	}
	return false;
}

/**
 * 把返回的数据集转换成Tree
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 * @return array
 */
function list_to_tree($list, $pk='id', $pid = 'pid', $child = '_child', $root = 0) {
	// 创建Tree
	$tree = array();
	if(is_array($list)) {
		// 创建基于主键的数组引用
		$refer = array();
		foreach ($list as $key => $data) {
			$refer[$data[$pk]] =& $list[$key];
		}
		foreach ($list as $key => $data) {
			// 判断是否存在parent
			$parentId =  $data[$pid];
			if ($root == $parentId) {
				$tree[] =& $list[$key];
			}else{
				if (isset($refer[$parentId])) {
					$parent =& $refer[$parentId];
					$parent[$child][] =& $list[$key];
				}
			}
		}
	}
	return $tree;
}

/**
 * 将list_to_tree的树还原成列表
 * @param  array $tree  原来的树
 * @param  string $child 孩子节点的键
 * @param  string $order 排序显示的键，一般是主键 升序排列
 * @param  array  $list  过渡用的中间数组，
 * @return array        返回排过序的列表数组
 */
function tree_to_list($tree, $child = '_child', $order='id', &$list = array()){
	if(is_array($tree)) {
		$refer = array();
		foreach ($tree as $key => $value) {
			$reffer = $value;
			if(isset($reffer[$child])){
				unset($reffer[$child]);
				tree_to_list($value[$child], $child, $order, $list);
			}
			$list[] = $reffer;
		}
		$list = list_sort_by($list, $order, $sortby='asc');
	}
	return $list;
}

/**
 * 格式化字节大小
 * @param  number $size      字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 */
function format_bytes($size, $delimiter = '') {
	$units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
	for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
	return round($size, 2) . $delimiter . $units[$i];
}

/**
 * 设置跳转页面URL
 * 使用函数再次封装，方便以后选择不同的存储方式（目前使用cookie存储）
 */
function set_redirect_url($url){
	cookie('redirect_url', $url);
}

/**
 * 获取跳转页面URL
 * @return string 跳转页URL
 */
function get_redirect_url(){
	$url = cookie('redirect_url');
	return empty($url) ? __APP__ : $url;
}

/**
 * 处理插件钩子
 * @param string $hook   钩子名称
 * @param mixed $params 传入参数
 * @return void
 */
function hook($hook,$params=array()){
	\Think\Hook::listen($hook,$params);
}
function get_index_url(){
	$damain=$_SERVER['SERVER_NAME'];
	$url="http://".$damain.__ROOT__;
	return $url;
}
function get_nav_url($url){
	switch ($url) {
		case 'http://' === substr($url, 0, 7):
		case '#' === substr($url, 0, 1):
			break;
		default:
			$url = U($url);
			break;
	}
	return $url;
}
/**
 * 获取插件类的类名
 * @param strng $name 插件名
 */
function get_addon_class($name){
	$class = "Addons\\{$name}\\{$name}Addon";
	return $class;
}

/**
 * 获取插件类的配置文件数组
 * @param string $name 插件名
 */
function get_addon_config($name){
	$class = get_addon_class($name);
	if(class_exists($class)) {
		$addon = new $class();
		return $addon->getConfig();
	}else {
		return array();
	}
}

/**
 * 插件显示内容里生成访问插件的url
 * @param string $url url
 * @param array $param 参数
 */
function addons_url($url, $param = array()){
	$url        = parse_url($url);
	$case       = C('URL_CASE_INSENSITIVE');
	$addons     = $case ? parse_name($url['scheme']) : $url['scheme'];
	$controller = $case ? parse_name($url['host']) : $url['host'];
	$action     = trim($case ? strtolower($url['path']) : $url['path'], '/');

	/* 解析URL带的参数 */
	if(isset($url['query'])){
		parse_str($url['query'], $query);
		$param = array_merge($query, $param);
	}

	/* 基础参数 */
	$params = array(
        '_addons'     => $addons,
        '_controller' => $controller,
        '_action'     => $action,
	);
	$params = array_merge($params, $param); //添加额外参数

	return U('Addons/execute', $params);
}

/**
 * 时间戳格式化
 * @param int $time
 * @return string 完整的时间显示
 */
function time_format($time = NULL,$format='Y-m-d H:i'){
	$time = $time === NULL ? NOW_TIME : intval($time);
	return date($format, $time);
}
/**
 * 时间区间条件
 * @param string $time
 */
function time_between($time_field = '',$alias='',$true_field=''){
	$map			= array();
	if (!empty($time_field)){
		$time_s				= $_GET[$time_field . '_s'];
		$time_e				= $_GET[$time_field . '_e'];
		$times				= array();
		if (!empty($time_s)){
			$times[]	= array('egt',strtotime($time_s));
		}
		if (!empty($time_e)){
			$times[]	= array('elt',strtotime($time_e)+86400);
		}
		if (!empty($times)){
			//$fields			= empty($alias) ? $time_field : $alias . '.' . $time_field;
			if(!empty($true_field)){
				$fields = $true_field;
			}else{
				$fields = $time_field;
			}
			if(!empty($alias)){
				$fields = $alias . '.' . $fields;
			}
			$map[$fields]	= array($times);
		}
	}
	return $map;
}
/**
 * 根据用户ID获取用户名
 * @param  integer $uid 用户ID
 * @return string       用户名
 */
function get_username($uid = 0){
	static $list;
	if(!($uid && is_numeric($uid))){ //获取当前登录用户名
		return session('user_auth.username');
	}

	/* 获取缓存数据 */
	if(empty($list)){
		$list = S('sys_active_user_list');
	}

	/* 查找用户信息 */
	$key = "u{$uid}";
	if(isset($list[$key])){ //已缓存，直接使用
		$name = $list[$key];
	} else { //调用接口获取用户信息
		$User = new User\Api\UserApi();
		$info = $User->info($uid);
		if($info && isset($info[1])){
			$name = $list[$key] = $info[1];
			/* 缓存用户 */
			$count = count($list);
			$max   = C('USER_MAX_CACHE');
			while ($count-- > $max) {
				array_shift($list);
			}
			S('sys_active_user_list', $list);
		} else {
			$name = '';
		}
	}
	return $name;
}

/**
 * 根据用户ID获取用户昵称
 * @param  integer $uid 用户ID
 * @return string       用户昵称
 */
function get_nickname($uid = 0){
	static $list;
	if(!($uid && is_numeric($uid))){ //获取当前登录用户名
		return session('user_auth.username');
	}

	/* 获取缓存数据 */
	if(empty($list)){
		$list = S('sys_user_nickname_list');
	}

	/* 查找用户信息 */
	$key = "u{$uid}";
	if(isset($list[$key])){ //已缓存，直接使用
		$name = $list[$key];
	} else { //调用接口获取用户信息
		$info = M('Member')->field('nickname')->find($uid);
		if($info !== false && $info['nickname'] ){
			$nickname = $info['nickname'];
			$name = $list[$key] = $nickname;
			/* 缓存用户 */
			$count = count($list);
			$max   = C('USER_MAX_CACHE');
			while ($count-- > $max) {
				array_shift($list);
			}
			S('sys_user_nickname_list', $list);
		} else {
			$name = '';
		}
	}
	return $name;
}

/**
 * 获取分类信息并缓存分类
 * @param  integer $id    分类ID
 * @param  string  $field 要获取的字段名
 * @return string         分类信息
 */
function get_category($id, $field = null){
	static $list;

	/* 非法分类ID */
	if(empty($id) || !is_numeric($id)){
		return '';
	}

	/* 读取缓存数据 */
	if(empty($list)){
		$list = S('sys_category_list');
	}
	/* 获取分类名称 */
	if(!isset($list[$id])){
		$cate = M('Category')->find($id);
		if(!$cate || 1 != $cate['status']){ //不存在分类，或分类被禁用
			return '';
		}
		$list[$id] = $cate;
		S('sys_category_list', $list); //更新缓存
	}
	return is_null($field) ? $list[$id] : $list[$id][$field];
}

/* 根据ID获取分类标识 */
function get_category_name($id){
	return get_category($id, 'name');
}

/* 根据ID获取分类名称 */
function get_category_title($id){
	return get_category($id, 'title');
}

/**
 * 获取文档模型信息
 * @param  integer $id    模型ID
 * @param  string  $field 模型字段
 * @return array
 */
function get_document_model($id = null, $field = null){
	static $list;

	/* 非法分类ID */
	if(!(is_numeric($id) || is_null($id))){
		return '';
	}

	/* 读取缓存数据 */
	if(empty($list)){
		$list = S('DOCUMENT_MODEL_LIST');
	}

	/* 获取模型名称 */
	if(empty($list)){
		$map   = array('status' => 1, 'extend' => 1);
		$model = M('Model')->where($map)->field(true)->select();
		foreach ($model as $value) {
			$list[$value['id']] = $value;
		}
		S('DOCUMENT_MODEL_LIST', $list); //更新缓存
	}

	/* 根据条件返回数据 */
	if(is_null($id)){
		return $list;
	} elseif(is_null($field)){
		return $list[$id];
	} else {
		return $list[$id][$field];
	}
}
/**
 * 获取独立模型信息
 * @param  integer $id    模型ID
 * @param  string  $field 模型字段
 * @return array
 */
function get_extend_model($id = null, $field = null){
	/* 非法分类ID */
	if(!(is_numeric($id) || is_null($id))){
		return '';
	}

	/* 读取缓存数据 */
	$info = S('DOCUMENT_EXTEND_INFO');

	/* 获取模型名称 */
	if(empty($info)){
		$map   	= array('status' => 1, 'extend' => 0,'id'=>$id);
		$info 	= M('Model')->where($map)->field(true)->find();
		S('DOCUMENT_EXTEND_INFO', $info); //更新缓存
	}
	if(is_null($field)){
		return $info;
	} else {
		return $info[$field];
	}
}
/**
 * 解析UBB数据
 * @param string $data UBB字符串
 * @return string 解析为HTML的数据
 */
function ubb($data){
	//TODO: 待完善，目前返回原始数据
	return $data;
}
/**
 * 生成随机数
 * @param number $length 字符串长度
 * @param number $type 字符串类型
 * @return string
 */
function randomString($length, $type = 0) {
	$arr  = array(
			0 => '123456789',
			1 => 'abcdefghjkmnpqrstuxy',
			2 => 'ABCDEFGHJKMNPQRSTUXY',
			3 => '123456789abcdefghjkmnpqrstuxy',
			4 => '123456789ABCDEFGHJKMNPQRSTUXY',
			5 => 'abcdefghjkmnpqrstuxyABCDEFGHJKMNPQRSTUXY',
			6 => '123456789abcdefghjkmnpqrstuxyABCDEFGHJKMNPQRSTUXY',
			7 => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
	);
	$chars = $arr[$type] ? $arr[$type] : $arr[7];
	$hash  = '';
	$max   = strlen($chars) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $chars[mt_rand(0, $max)];
	}
	return $hash;
}

/**
 * 记录行为日志，并执行该行为的规则
 * @param string $action 行为标识
 * @param string $model 触发行为的模型名
 * @param int $record_id 触发行为的记录id
 * @param int $user_id 执行行为的用户id
 * @return boolean

 */
function action_log($logs='',$model = null, $record_id = null, $user_id = null){
	$user_id 		= empty($user_id) ? is_login() : $user_id;
	//参数检查
	if(empty($logs) || empty($model) || empty($record_id) || empty($user_id)){
		return false;
	}

	//插入行为日志
	$data['logs']			= $logs;
	$data['user_id']        = $user_id;
	$data['action_ip']      = ip2long(get_client_ip());
	$data['model']          = $model;
	$data['record_id']      = $record_id;
	$data['create_time']    = NOW_TIME;
	$data['url']     		= $_SERVER['REQUEST_URI'];
	M('ActionLog')->add($data);
}

/**
 * 解析行为规则
 * 规则定义  table:$table|field:$field|condition:$condition|rule:$rule[|cycle:$cycle|max:$max][;......]
 * 规则字段解释：table->要操作的数据表，不需要加表前缀；
 *              field->要操作的字段；
 *              condition->操作的条件，目前支持字符串，默认变量{$self}为执行行为的用户
 *              rule->对字段进行的具体操作，目前支持四则混合运算，如：1+score*2/2-3
 *              cycle->执行周期，单位（小时），表示$cycle小时内最多执行$max次
 *              max->单个周期内的最大执行次数（$cycle和$max必须同时定义，否则无效）
 * 单个行为后可加 ； 连接其他规则
 * @param string $action 行为id或者name
 * @param int $self 替换规则里的变量为执行用户的id
 * @return boolean|array: false解析出错 ， 成功返回规则数组

 */
function parse_action($action = null, $self){
	if(empty($action)){
		return false;
	}

	//参数支持id或者name
	if(is_numeric($action)){
		$map = array('id'=>$action);
	}else{
		$map = array('name'=>$action);
	}

	//查询行为信息
	$info = M('Action')->where($map)->find();
	if(!$info || $info['status'] != 1){
		return false;
	}

	//解析规则:table:$table|field:$field|condition:$condition|rule:$rule[|cycle:$cycle|max:$max][;......]
	$rules = $info['rule'];
	$rules = str_replace('{$self}', $self, $rules);
	$rules = explode(';', $rules);
	$return = array();
	foreach ($rules as $key=>&$rule){
		$rule = explode('|', $rule);
		foreach ($rule as $k=>$fields){
			$field = empty($fields) ? array() : explode(':', $fields);
			if(!empty($field)){
				$return[$key][$field[0]] = $field[1];
			}
		}
		//cycle(检查周期)和max(周期内最大执行次数)必须同时存在，否则去掉这两个条件
		if(!array_key_exists('cycle', $return[$key]) || !array_key_exists('max', $return[$key])){
			unset($return[$key]['cycle'],$return[$key]['max']);
		}
	}

	return $return;
}

//基于数组创建目录和文件
function create_dir_or_files($files){
	foreach ($files as $key => $value) {
		if(substr($value, -1) == '/'){
			mkdir($value);
		}else{
			@file_put_contents($value, '');
		}
	}
}

if(!function_exists('array_column')){
	function array_column(array $input, $columnKey, $indexKey = null) {
		$result = array();
		if (null === $indexKey) {
			if (null === $columnKey) {
				$result = array_values($input);
			} else {
				foreach ($input as $row) {
					$result[] = $row[$columnKey];
				}
			}
		} else {
			if (null === $columnKey) {
				foreach ($input as $row) {
					$result[$row[$indexKey]] = $row;
				}
			} else {
				foreach ($input as $row) {
					$result[$row[$indexKey]] = $row[$columnKey];
				}
			}
		}
		return $result;
	}
}

/**
 * 获取表名（不含表前缀）
 * @param string $model_id
 * @return string 表名

 */
function get_table_name($model_id = null){
	if(empty($model_id)){
		return false;
	}
	$Model = M('Model');
	$name = '';
	$info = $Model->getById($model_id);
	if($info['extend'] != 0){
		$name = $Model->getFieldById($info['extend'], 'name').'_';
	}
	$name .= $info['name'];
	return $name;
}
function get_group_list($uid)
{
	$gid		= array();
	$grouplist 	= M('auth_group_access')->where(array('uid'=>$uid))->select();
	if (!empty($grouplist))
	{
		foreach ($grouplist as $key=>$val)
		{
			$gid['gid'][$val['group_id']] 	= $val['group_id'];
			$gid['uid'][$val['uid']][] 		= $val['uid'];
		}
	}
	return $gid;
}
function get_group($uid){
	$gid		= '0';
	$groupRow 	= M('auth_group_access')->where(array('uid'=>$uid))->find();
	if (!empty($groupRow))
	{
		$gid	= $groupRow['group_id'];
	}
	return $gid;
}


/**
 * 获取属性信息并缓存
 * @param  integer $id    属性ID
 * @param  string  $field 要获取的字段名
 * @return string         属性信息
 */
function get_model_attribute($model_id, $group = true){
	static $list;

	/* 非法ID */
	if(empty($model_id) || !is_numeric($model_id)){
		return '';
	}

	/* 读取缓存数据 */
	if(empty($list)){
		$list = S('attribute_list');
	}

	/* 获取属性 */
	if(!isset($list[$model_id])){
		$map = array('model_id'=>$model_id);
		$extend = M('Model')->getFieldById($model_id,'extend');

		if($extend){
			$map = array('model_id'=> array("in", array($model_id, $extend)));
		}
		$info = M('Attribute')->where($map)->select();
		$list[$model_id] = $info;
		//S('attribute_list', $list); //更新缓存
	}

	$attr = array();
	foreach ($list[$model_id] as $value) {
		$attr[$value['id']] = $value;
	}

	if($group){
		$sort  = M('Model')->getFieldById($model_id,'field_sort');

		if(empty($sort)){	//未排序
			$group = array(1=>array_merge($attr));
		}else{
			$group = json_decode($sort, true);

			$keys  = array_keys($group);
			foreach ($group as &$value) {
				foreach ($value as $key => $val) {
					$value[$key] = $attr[$val];
					unset($attr[$val]);
				}
			}

			if(!empty($attr)){
				$group[$keys[0]] = array_merge($group[$keys[0]], $attr);
			}
		}
		$attr = $group;
	}
	return $attr;
}

/**
 * 调用系统的API接口方法（静态方法）
 * api('User/getName','id=5'); 调用公共模块的User接口的getName方法
 * api('Admin/User/getName','id=5');  调用Admin模块的User接口
 * @param  string  $name 格式 [模块名]/接口名/方法名
 * @param  array|string  $vars 参数
 */
function api($name,$vars=array()){
	$array     = explode('/',$name);
	$method    = array_pop($array);
	$classname = array_pop($array);
	$module    = $array? array_pop($array) : 'Common';
	$callback  = $module.'\\Api\\'.$classname.'Api::'.$method;
	if(is_string($vars)) {
		parse_str($vars,$vars);
	}
	return call_user_func_array($callback,$vars);
}

/**
 * 根据条件字段获取指定表的数据
 * @param mixed $value 条件，可用常量或者数组
 * @param string $condition 条件字段
 * @param string $field 需要返回的字段，不传则返回整个数据
 * @param string $table 需要查询的表

 */
function get_table_field($value = null, $condition = 'id', $field = null, $table = null){
	if(empty($value) || empty($table)){
		return false;
	}

	//拼接参数
	$map[$condition] = $value;
	$info = M(ucfirst($table))->where($map);
	if(empty($field)){
		$info = $info->field(true)->find();
	}else{
		$info = $info->getField($field);
	}
	return $info;
}

/**
 * 获取链接信息
 * @param int $link_id
 * @param string $field
 * @return 完整的链接信息或者某一字段

 */
function get_link($link_id = null, $field = 'url'){
	$link = '';
	if(empty($link_id)){
		return $link;
	}
	$link = M('Url')->getById($link_id);
	if(empty($field)){
		return $link;
	}else{
		return $link[$field];
	}
}

/**
 * 获取文档封面图片
 * @param int $cover_id
 * @param string $field
 * @return 完整的数据  或者  指定的$field字段值

 */
function get_cover($cover_id, $field = null){
	if(empty($cover_id)){
		return false;
	}
	$picture = M('Picture')->where(array('status'=>1))->getById($cover_id);
	return empty($field) ? $picture : $picture[$field];
}

function get_cover2($cover_id){
	if(empty($cover_id)){
		return '';
	}
	$pics		= '';
	$picture 	= M('Picture')->where(array('status'=>1))->field('path')->getById($cover_id);
	if (!empty($picture)){
		$picture	= $picture['path'];
		if (strpos($picture, 'Uploads/')){
			$picture				= trim('./'.trim($picture,'./'),'.');
			$pics					= 'http://'.WEB_DOMAIN . $picture;
		}else{
			$pics					= $picture;
		}
	}
	return $pics;
}

function get_cover3($pic){
	if(empty($pic)){
		return '';
	}
	$pics		= '';
	if (!empty($pic)){
		if (strpos($pic, 'Uploads/')){
			$pic				    = trim('./'.trim($pic,'./'),'.');
			$pics					= 'http://'.WEB_DOMAIN . $pic;
		}else{
			$pics					= $pic;
		}
	}
	return $pics;
}

function is_exist_user($platform_id){
	$member = M('Member')->field('uid')->where(array('is_system_admin'=>1,'platform_id'=>$platform_id))->find();
	if($member['uid']>0){
		return 1;
	}
	return 0;
}

//获取文件PATH
function get_file_path($cover_id,$thumSize='',$def=''){
	if(empty($cover_id)){
		return $def;
	}
	$picture 	= M('Picture')->where(array('status'=>1,'id'=>$cover_id))->getField('path');
	if (empty($picture)){
		return $def;
	}
	$path		= explode('/', trim($picture,'/'));
	$nums		= count($path);
	$filename	= $path[$nums-1];
	$filepath	= $picture;
	if (!empty($thumSize)){
		$filepath	= str_replace($filename, 'thumb/'.$thumSize.'-'.$filename, $picture);
		if (!file_exists(trim($filepath,'/'))){
			$filepath	= $picture;
		}
	}
	return (!empty($filepath) && file_exists(trim($filepath,'/'))) ? 'http://'.WEB_DOMAIN.$filepath : $def;
}
/**
 * 检查$pos(推荐位的值)是否包含指定推荐位$contain
 * @param number $pos 推荐位的值
 * @param number $contain 指定推荐位
 * @return boolean true 包含 ， false 不包含

 */
function check_document_position($pos = 0, $contain = 0){
	if(empty($pos) || empty($contain)){
		return false;
	}

	//将两个参数进行按位与运算，不为0则表示$contain属于$pos
	$res = $pos & $contain;
	if($res !== 0){
		return true;
	}else{
		return false;
	}
}

/**
 * 获取数据的所有子孙数据的id值
 */

function get_stemma($pids,Model &$model, $field='id'){
	$collection = array();

	//非空判断
	if(empty($pids)){
		return $collection;
	}

	if( is_array($pids) ){
		$pids = trim(implode(',',$pids),',');
	}
	$result     = $model->field($field)->where(array('pid'=>array('IN',(string)$pids)))->select();
	$child_ids  = array_column ((array)$result,'id');

	while( !empty($child_ids) ){
		$collection = array_merge($collection,$result);
		$result     = $model->field($field)->where( array( 'pid'=>array( 'IN', $child_ids ) ) )->select();
		$child_ids  = array_column((array)$result,'id');
	}
	return $collection;
}
/*
 * 字符串加密
 * */
function FauthCode($string,$operation='ENCODE'){
	$ckey_length=4;
	// 随机密钥长度 取值 0-32;
	// 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
	// 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
	// 当此值为 0 时，则不产生随机密钥
	$string=isset($string)?$string:'';
	$key=md5(C('DATA_AUTH_KEY'));
	$keya=md5(substr($key,0,16));
	$keyb=md5(substr($key,16,16));
	$keyc=$ckey_length?($operation=='DECODE'?substr($string,0,$ckey_length):substr(md5(microtime()),-$ckey_length)):'';
	$cryptkey=$keya.md5($keya.$keyc);
	$key_length=strlen($cryptkey);
	$string=$operation=='DECODE'?base64_decode(substr($string,$ckey_length)):sprintf('%010d',0).substr(md5($string.$keyb),0,16).$string;
	$string_length=strlen($string);
	$result='';
	$box=range(0,255);
	$rndkey=array();
	for($i=0;$i<=255;$i++){
		$rndkey[$i]=ord($cryptkey[$i%$key_length]);
	}
	for($j=$i=0;$i<256;$i++){
		$j=($j+$box[$i]+$rndkey[$i])%256;
		$tmp=$box[$i];
		$box[$i]=$box[$j];
		$box[$j]=$tmp;
	}
	for($a=$j=$i=0;$i<$string_length;$i++){
		$a=($a+1)%256;
		$j=($j+$box[$a])%256;
		$tmp=$box[$a];
		$box[$a]=$box[$j];
		$box[$j]=$tmp;
		$result.=chr(ord($string[$i])^($box[($box[$a]+$box[$j])%256]));
	}
	if($operation=='DECODE'){
		if((substr($result,0,10)==0||substr($result,0,10)>0)&&substr($result,10,16)==substr(md5(substr($result,26).$keyb),0,16)){
			return substr($result,26);
		}else{
			return '';
		}
	}else{
		return $keyc.str_replace('=','',base64_encode($result));
	}
}
function array_filter_backcall($val)
{
	return ($val===''||$val===null) ? false : true;
}
//过滤空格 中文逗号等非法子都 并转为英文逗号
function change_string_code($str = ''){
	if(!empty($str)){
		$str = trim($str,',');
		$str = trim($str,'，');
		$str = trim($str,PHP_EOL);
		$str = str_replace('，', ',', $str);
		$str = str_replace(PHP_EOL, ',', $str);
		$str = str_replace(' ', ',', $str);
	}
	return implode(',', array_filter(explode(',', $str)));
}
/**
 * 验证分类是否允许发布内容
 * @param  integer $id 分类ID
 * @return boolean     true-允许发布内容，false-不允许发布内容
 */
function check_category($id){
	if (is_array($id)) {
		$type = get_category($id['category_id'], 'type');
		$type = explode(",", $type);
		return in_array($id['type'], $type);
	} else {
		$publish = get_category($id, 'allow_publish');
		return $publish ? true : false;
	}
}
//获取ip地址信息，返回操作对象
function get_ip_address(){
	$ip			= getip();
	$json		= CurlHttp("http://ip.taobao.com/service/getIpInfo2.php",array('ip'=>$ip),'POST');//根据taobao ip
	$jsonarr	= json_decode($json);
	if($jsonarr->code==0)
	{
		$data 	= $jsonarr->data;
		return $data;
	}
	else
	{
		return false;
	}
}
//从服务器获取访客ip
function getip(){
	$onlineip = "";
	if(getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) {
		$onlineip = getenv("HTTP_CLIENT_IP");
	} elseif(getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) {
		$onlineip = getenv("HTTP_X_FORWARDED_FOR");
	} elseif(getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) {
		$onlineip = getenv("REMOTE_ADDR");
	} elseif(isset($_SERVER["REMOTE_ADDR"]) && $_SERVER["REMOTE_ADDR"] && strcasecmp($_SERVER["REMOTE_ADDR"], "unknown")) {
		$onlineip = $_SERVER["REMOTE_ADDR"];
	}
	return safe_replace($onlineip);
}
function CurlHttp($url,$body='',$method='DELETE',$headers=array()){
	$httpinfo=array();
	$ci=curl_init();
	/* Curl settings */
	curl_setopt($ci,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_0);
	curl_setopt($ci,CURLOPT_USERAGENT,'toqi.net');
	curl_setopt($ci,CURLOPT_CONNECTTIMEOUT,30);
	curl_setopt($ci,CURLOPT_TIMEOUT,30);
	curl_setopt($ci,CURLOPT_RETURNTRANSFER,TRUE);
	curl_setopt($ci,CURLOPT_ENCODING,'');
	curl_setopt($ci,CURLOPT_SSL_VERIFYPEER,FALSE);
	curl_setopt($ci,CURLOPT_HEADER,FALSE);
	switch($method){
		case 'POST':
			curl_setopt($ci,CURLOPT_POST,TRUE);
			if(!empty($body)){
				curl_setopt($ci,CURLOPT_POSTFIELDS,$body);
			}
			break;
		case 'DELETE':
			curl_setopt($ci,CURLOPT_CUSTOMREQUEST,'DELETE');
			if(!empty($body)){
				$url=$url.'?'.str_replace('amp;', '', http_build_query($body));
			}
	}
	curl_setopt($ci,CURLOPT_URL,$url);
	curl_setopt($ci,CURLOPT_HTTPHEADER,$headers);
	curl_setopt($ci,CURLINFO_HEADER_OUT,TRUE);
	$response=curl_exec($ci);
	$httpcode=curl_getinfo($ci,CURLINFO_HTTP_CODE);
	$httpinfo=array_merge($httpinfo,curl_getinfo($ci));
	curl_close($ci);
	return $response;
}
/* 访问统计 */
function IpLookup($ip='',$status,$id){
	$arr				= get_ip_address();
	if ($arr->country == '未分配或者内网IP') return false;
	$ip					= $arr->ip;
	$data["ip"]			= $arr->ip;
	$data["country"]	= $arr->country;
	$data["province"]	= $arr->region;
	$data["city"]		= $arr->city;
	$data["isp"]		= $arr->isp;
	if(is_login()){
		$member=D("member");
		$data["uid"]=$member->uid();
	}
	if(!empty($status))
	{
		$data["status"]=$status;
	}
	if(!empty($id))
	{$data["page"]=$id;}
	$data["time"]=NOW_TIME;
	$data["referer"]=$_SERVER['HTTP_REFERER'];
	$data["url"]=$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$record=M("records");
	if($record->where("ip='$ip' and status='$status' and page='$id'")->select()){
		//有访问记录
		$now=NOW_TIME;
		$recordtime=date("YmdH",$now);//当前时间点
		$time=$record->where("ip='$ip' and status='$status' and page='$id'")->limit(1)->order("id desc")->getField("time");
		$visittime=date("YmdH",$time);//获取最近一次访问点
		$chazhi=$recordtime-$visittime;//小时差值
		if($chazhi>C('LAG')){
			$record->add($data);
		}//每隔5小时记录一次
		else{
		}//不记录

	}
	else{//没有访问记录
		$record->add($data);
	}

	return $status;
}
/* 记录登录历史信息 ,会员模型函数 */
function history($uid){
	$arr					= get_ip_address();
	$data["uid"]			= $uid;
	$data["login_ip"]		= $arr->ip;
	$data["login_country"]	= $arr->country;
	$data["login_province"]	= $arr->region;
	$data["login_city"]		= $arr->city;
	$data["login_isp"]		= $arr->isp;
	$data["login_time"]		= NOW_TIME;
	/* 登录方式 */
	$data["login_way"]		= isMobil();
	$history				= M("history");
	$data					= $history->create($data);
	$history->add($data);
}
/* 判断是电脑还是手机访问*/
function isMobil()
{
	$useragent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
	$useragent_commentsblock = preg_match('|\(.*?\)|', $useragent, $matches) > 0 ? $matches[0] : '';
	$mobile_os_list = array
	(
            'Google Wireless Transcoder',
            'Windows CE',
            'WindowsCE',
            'Symbian',
            'Android',
            'armv6l',
            'armv5',
            'Mobile',
            'CentOS',
            'mowser',
            'AvantGo',
            'Opera Mobi',
            'J2ME/MIDP',
            'Smartphone',
            'Go.Web',
            'Palm',
            'iPAQ'
            );
            $mobile_token_list = array
            (
            'Profile/MIDP',
            'Configuration/CLDC-',
            '160×160',
            '176×220',
            '240×240',
            '240×320',
            '320×240',
            'UP.Browser',
            'UP.Link',
            'SymbianOS',
            'PalmOS',
            'PocketPC',
            'SonyEricsson',
            'Nokia',
            'BlackBerry',
            'Vodafone','BenQ',
            'Novarra-Vision',
            'Iris',
            'NetFront',
            'HTC_',
            'Xda_',
            'SAMSUNG-SGH',
            'Wapaka',
            'DoCoMo',
            'iPhone',
            'iPod'
            );
            $found_mobile =CheckSubstrs($mobile_os_list, $useragent_commentsblock) ||
            CheckSubstrs($mobile_token_list, $useragent);
            if ($found_mobile)
            {
            	$way= '手机登录';//'手机登录'
            }
            else
            {
            	$way= '电脑登录';//'电脑登录'
            }
            return $way;

}
function CheckSubstrs($substrs,$text){
	foreach($substrs as $substr)
	{
		if(false!==strpos($text,$substr))
		{
			return true;
		}
		return false;
	}
}
/**
 *手机号格式验证
 */
function Mobile_check($mobile,$type = array()){
	$res[1]	= preg_match('/^1(3[0-9]|5[0-35-9]|7[0-9]|8[0-9]|9[0-9])\\d{8}$/', $mobile);//手机号码 移动|联通|电信
	$res[2]	= preg_match('/^1(34[0-8]|(3[5-9]|5[017-9]|8[0-9])\\d)\\d{7}$/', $mobile);//中国移动
	$res[3]	= preg_match('/^1(3[0-2]|5[256]|8[56])\\d{8}$/', $mobile);//中国联通
	$res[4]	= preg_match('/^1((33|53|8[09])[0-9]|349)\\d{7}$/', $mobile);//中国电信
	$res[5]	= preg_match('/^0(10|2[0-5789]|\\d{3})-\\d{7,8}$/', $mobile);//大陆地区固话及小灵通
	$type	= empty($type) ? array(1,2,3,4,5) : $type;
	$ok 	= false;
	foreach ($type as $key=>$val)
	{
		if ($res[$val]) $ok = true;
		continue;
	}
	return ($mobile && $ok) ? true : false;
}
//合法邮箱
function Email_check($email){
	return (!preg_match('#[a-z0-9&\-_.]+@[\w\-_]+([\w\-.]+)?\.[\w\-]+#is', $email) || !$email) ? false : true;
}
/**
 * 系统日志
 */
function addUserLog($title,$uid) {
	if(empty($uid))  return false;
	$data ['create_time'] = time();
	$data ['update_time'] = time();
	$data ['title'] = $title;
	$data ['uid'] =$uid;
	M ( 'UserLog' )->add ( $data );
	return true;
}
//文件写入，快捷调试
function wr($data,$file = 'info.txt',$return=true){
	$return == true ? file_put_contents($_SERVER['DOCUMENT_ROOT'].'/Data/'.$file,var_export($data,true),FILE_APPEND) : file_put_contents($_SERVER['DOCUMENT_ROOT'].'/Data/'.$file,var_export($data,true));
}
//数据库写入，快捷调试
function dblog($data){
	//数据库
	$data	= array($data,date('Y-m-d H:i:s',NOW_TIME));
	$data	= is_array($data) ? json_encode($data) : $data;
	M('dblog')->add(array('msg'=>$data,'date'=>date('Y-m-d H:i:s',NOW_TIME)));
}
//邮件发送函数
function sendMail($to, $title, $content){
	Vendor('PHPMailer.PHPMailer');
	$mail = new \vendor\PHPMailer\PHPMailer(); //实例化
	$mail->IsSMTP(); // 启用SMTP
	$mail->Host			= C('MAIL_HOST'); //smtp服务器的名称（这里以QQ邮箱为例）
	$mail->SMTPAuth 	= true; //启用smtp认证
	$mail->Username 	= C('MAIL_USERNAME'); //你的邮箱名
	$mail->Password 	= C('MAIL_PASSWORD') ; //邮箱密码
	$mail->From 		= C('MAIL_USERNAME'); //发件人地址（也就是你的邮箱地址）
	$mail->FromName 	= C('MAIL_FROMNAME'); //发件人姓名
	$mail->AddAddress($to,"尊敬的客户");
	$mail->WordWrap 	= 50; //设置每行字符长度
	$mail->IsHTML(TRUE); // 是否HTML格式邮件
	$mail->CharSet		= 'utf-8'; //设置邮件编码
	$mail->Subject 		= $title; //邮件主题
	$mail->Body 		= $content; //邮件内容
	$mail->AltBody 		= "这是一个纯文本的身体在非营利的HTML电子邮件客户端"; //邮件正文不支持HTML的备用显示
	return($mail->Send());
}
function makeQrcode($urls,$fileName=false,$level='L',$size=4)
{
	vendor("Qrcode.QRcode");
	$QRmodel 	= new \QRcode();
	return $QRmodel->png($urls, $fileName, $level, $size, 2);
}
function mkDirs($dir){
	if(!is_dir($dir)){
		if(!mkDirs(dirname($dir))){
			return false;
		}
		if(!mkdir($dir,0775)){
			return false;
		}
	}
	return true;
}
function delDirAndFile($path, $delDir = false) {
	if (is_array($path)) {
		foreach ($path as $subPath)
		$this->delDirAndFile($subPath, $delDir);
	}
	if (is_dir($path)) {
		$handle = opendir($path);
		if ($handle) {
			while (false !== ( $item = readdir($handle) )) {
				if ($item != "." && $item != "..")
				is_dir("$path/$item") ? $this->delDirAndFile("$path/$item", $delDir) : unlink("$path/$item");
			}
			closedir($handle);
			if ($delDir)
			return rmdir($path);
		}
	} else {
		if (file_exists($path)) {
			return unlink($path);
		} else {
			return false;
		}
	}
	clearstatcache();
}
//获得字段加密HASH值
function get_fields_hash($fields)
{
	$string 		= '';
	if (!empty($fields)){
		$fields		= is_string($fields) ? explode(',', $fields) : $fields;
		$fields		= array_flip($fields);
		ksort($fields);
		$fields		= array_flip($fields);
		foreach ($fields as $v){
			$string .= $v;
		}
	}
	return md5($string.C('DATA_AUTH_KEY'));;
}

//获取短信验证码
function msg_sendcode($moblie,$content)
{
	$flag         		= 0;  //要post的数据
	$content      		= iconv( "UTF-8", "gb2312" ,$content);
	$argv['sn']     	= C('API_SMS_SN');//序列号
	$argv['pwd']    	= strtoupper(md5(C('API_SMS_SN').C('API_SMS_PASS')));//此处密码需要加密 加密方式为 md5(sn+password) 32位大写
	$argv['mobile']   	= $moblie;//手机号 多个用英文的逗号隔开 post理论没有长度限制.推荐群发一次小于等于100
	$argv['content']  	= $content;//短信内容
	$argv['ext']    	= '';
	$argv['stime']    	= '';//定时时间 格式为2011-6-29 11:09:21
	$argv['rrid']   	= '';
	//构造要post的字符串
	foreach ($argv as $key=>$value){
		if ($flag!=0){
			$params .= "&";
			$flag = 1;
		}
		$params.= $key."="; 
		$params.= urlencode($value);
		$flag = 1;
	}
	$length = strlen($params);
	//创建socket连接
	$fp = fsockopen("sdk.entinfo.cn",8060,$errno,$errstr,10) or exit($errstr."--->".$errno);
	//构造post请求的头
	$header = "POST /webservice.asmx/mt HTTP/1.1\r\n";
	$header .= "Host:sdk.entinfo.cn\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: ".$length."\r\n";
	$header .= "Connection: Close\r\n\r\n";
	//添加post的字符串
	$header .= $params."\r\n";
	//发送post的数据
	fputs($fp,$header);
	$inheader = 1;
	while (!feof($fp)) {
		$line = fgets($fp,1024); //去除请求包的头只显示页面的返回数据
		if ($inheader && ($line == "\n" || $line == "\r\n")) {
			$inheader = 0;
		}
		if ($inheader == 0) {
			// echo $line;
		}
	}
	$line=str_replace("<string xmlns=\"http://tempuri.org/\">","",$line);
	$line=str_replace("</string>","",$line);
	$result=explode("-",$line);
	if(count($result)>1){
		return false;
	}else{
		return true;
	}
}

function msg_rand(){
	$rand = '';
	for($i=0;$i<6;$i++){
		$rand = $rand.rand(0,9);
	}
	return $rand;
}

//生成24位唯一订单号
function create_orderid(){
	return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
}

//获取有效期内的聚蚁币
function get_currency($uid){
	//过期改变状态
	M('userRechargeRecord')->where(array('create_time'=>array('elt',NOW_TIME-C('CURRENCY_VALIDITY')), 'uid'=>$uid))->save(array('is_expired'=>1));
	
	$userRechargeRecord = M('userRechargeRecord')->where(array('current_currency'=>array('gt',0), 'create_time'=>array('gt',NOW_TIME-C('CURRENCY_VALIDITY')), 'is_expired'=>0, 'uid'=>$uid))->order('create_time asc')->select();
	$currentCurrency = 0;
	if(!empty($userRechargeRecord)){
		foreach ($userRechargeRecord as $key=>$value){
			$currentCurrency += $value['current_currency'];
		}
	}
	return $currentCurrency;
}

/**
 * 火星坐标系(GCJ-02)转百度坐标系(BD-09) 谷歌、高德——>百度
 * @param lng 火星坐标经度
 * @param lat 火星坐标纬度
 * @return 百度坐标数组
 */
function gcj02tobd09($lng, $lat) {
	$x_pi = 3.14159265358979324 * 3000.0 / 180.0;
	$z = sqrt ( $lng * $lng + $lat * $lat ) + 0.00002 * sin ( $lat * $x_pi );
	$theta = atan2 ( $lat, $lng ) + 0.000003 * cos ( $lng * $x_pi );
	$bd_lng = $z * cos ( $theta ) + 0.0065;
	$bd_lat = $z * sin ( $theta ) + 0.006;
	return array ('longitude'=>$bd_lng, 'latitude'=>$bd_lat);
}

/**
 * 百度坐标系(BD-09)转火星坐标系(GCJ-02) 百度——>谷歌、高德
 * @param bd_lon 百度坐标纬度
 * @param bd_lat 百度坐标经度
 * @return 火星坐标数组
 */
function bd09togcj02($bd_lon, $bd_lat) {
	$x_pi = 3.14159265358979324 * 3000.0 / 180.0;
	$x = $bd_lon - 0.0065;
	$y = $bd_lat - 0.006;
	$z = sqrt ( $x * $x + $y * $y ) - 0.00002 * sin ( $y * $x_pi );
	$theta = atan2 ( $y, $x ) - 0.000003 * cos ( $x * $x_pi );
	$gg_lng = $z * cos ( $theta );
	$gg_lat = $z * sin ( $theta );
	return array ('longitude'=>$gg_lng, 'latitude'=>$gg_lat);
}

/**
 *  快递鸟post提交数据
 * @param  string $url 请求Url
 * @param  array $datas 提交的数据
 * @return url响应返回的html
 */
function sendPost($url, $datas) {
	$temps = array();
	foreach ($datas as $key => $value) {
		$temps[] = sprintf('%s=%s', $key, $value);
	}
	$post_data = implode('&', $temps);
	$url_info = parse_url($url);
	if(empty($url_info['port']))
	{
		$url_info['port']=80;
	}
	$httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
	$httpheader.= "Host:" . $url_info['host'] . "\r\n";
	$httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";
	$httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";
	$httpheader.= "Connection:close\r\n\r\n";
	$httpheader.= $post_data;
	$fd = fsockopen($url_info['host'], $url_info['port']);
	fwrite($fd, $httpheader);
	$gets = "";
	$headerFlag = true;
	while (!feof($fd)) {
		if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
			break;
		}
	}
	while (!feof($fd)) {
		$gets.= fread($fd, 128);
	}
	fclose($fd);

	return $gets;
}

/**
 * 快递鸟电商Sign签名生成
 * @param data 内容
 * @param appkey Appkey
 * @return DataSign签名
 */
function kdn_encrypt($data, $appkey) {
    return urlencode(base64_encode(md5($data.$appkey)));
}

/**
 * 判断小数点位数
 */
function judge_decimal($num, $digits=1){
	if($digits == 0){
		$rule = '/^([1-9]\d*|0)$/';
	}else{
		$rule = '/^[0-9]+(.[0-9]{1,'.$digits.'})?$/';
	}
	if (preg_match($rule, $num)) {
		return true;
	}else{
		return false;
	}
}


/**
 * 生成邀请码
 */
function randomCode() {
	$arr  = array(
			0 => '0123456789',
			1 => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
	);
	$chars	= $arr[0];
	$max 	= strlen($arr[0]) - 1;
	for($i = 0; $i < 4; $i++) {
		$hash1 .= $chars[mt_rand(0, $max)];
	}
	$chars	= $arr[1];
	$max 	= strlen($arr[1]) - 1;
	for($i = 0; $i < 2; $i++) {
		$hash2 .= $chars[mt_rand(0, $max)];
	}
	$code = str_shuffle($hash1.$hash2);
	
	$count = M('user')->where(array('code'=>$code))->count();
	if($count > 0){
		randomCode();
	}else{
		return $code;
	}
}

/**
 * 发送短信
 */
function sendcode($moblie, $code){

	vendor('DYSMS.SignatureHelper');
	$params = array ();

	// *** 需用户填写部分 ***

	// fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
	$accessKeyId = "LTAI7KCzBth0NKlP";
	$accessKeySecret = "Zx7b8lODKzCI9eandaBdK3eNZsL9UY";

	// fixme 必填: 短信接收号码
	$params["PhoneNumbers"] = $moblie;

	// fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
	$params["SignName"] = "省鑫";

	// fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
	$params["TemplateCode"] = "SMS_137820275";

	// fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
	$params['TemplateParam'] = Array (
			"code" => $code
			);

	// fixme 可选: 设置发送短信流水号
	//$params['OutId'] = "12345";

	// fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
	//$params['SmsUpExtendCode'] = "1234567";

	// *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
	if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
		$params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
	}

	// 初始化SignatureHelper实例用于设置参数，签名以及发送请求
	$helper = new Aliyun\DySDKLite\SignatureHelper();

	// 此处可能会抛出异常，注意catch
	$content = $helper->request(
			$accessKeyId,
			$accessKeySecret,
			"dysmsapi.aliyuncs.com",
			array_merge($params, array(
					"RegionId" => "cn-hangzhou",
					"Action" => "SendSms",
					"Version" => "2017-05-25",
			))
			// fixme 选填: 启用https
			// ,true
			);
	return true;
}
?>