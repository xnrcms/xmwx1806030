<?php
/**
 * 后台公共文件
 * 主要定义后台公共函数库
 */
/*格式化字段排列*/
function get_format_field($list_grid)
{
	$fields = array();
	$grids  = preg_split('/[;\r\n]+/s', $list_grid);

	foreach ($grids as &$value) {
		// 字段:标题:链接
		$val      = explode(':', $value);
		// 支持多个字段显示
		$field   = explode(',', $val[0]);
		$value    = array('field' => $field, 'title' => $val[1]);
		if(isset($val[2])){
			// 链接信息
			$value['href']  =   $val[2];
			// 搜索链接信息中的字段信息
			preg_replace_callback('/\[([a-z_]+)\]/', function($match) use(&$fields){$fields[]=$match[1];}, $value['href']);
		}
		if(strpos($val[1],'|')){
			// 显示格式定义
			list($value['title'],$value['format'])    =   explode('|',$val[1]);
		}
		if(isset($val[3])){
			//列百分比
			$value['width']  =   $val[3]>0 ? $val[3].'%' : '';
		}
		if(isset($val[4])){
			//字段类型
			$value['ftype']  =   !empty($val[4]) ? $val[4] : '';
		}
		//.....扩展更多
		foreach($field as $val){
			$array  =   explode('|',$val);
			$fields[] = $array[0];
		}
	}
	return $grids;
}
//字段处理
function get_fields_string($fields,$pre='')
{
	$arr = array();
	if (!empty($fields))
	{
		foreach ($fields as $key=>$val)
		{
			$arr[$val['field'][0]] = $pre != '' ? $pre.'.'.$val['field'][0] : $val['field'][0];
		}
		return implode(',', $arr);
	}
	return '';
}
/* 解析列表定义规则*/
function get_list_field($data, $grid){

	// 获取当前字段数据
	foreach($grid['field'] as $field){
		$array  =   explode('|',$field);
		$temp  =	$data[$array[0]];
		// 函数支持
		if(isset($array[1])){
			$temp = call_user_func($array[1], $temp);
		}
		$data2[$array[0]]    =   $temp;
	}
	if(!empty($grid['format'])){
		$func  		=   explode('(',$grid['format']);
		if (function_exists($func[0])) {
			$value	= exec(str_replace('$', $data2[$field], $grid['format']));
		}
		//$value  =   preg_replace_callback('/\[([a-z_]+)\]/', function($match) use($data2){return $data2[$match[1]];}, $grid['format']);
	}else{
		$value  =   implode(' ',$data2);
	}

	// 链接支持
	if(!empty($grid['href'])){
		$links  =   explode(',',$grid['href']);
		foreach($links as $link){
			$array  =   explode('|',$link);
			$href   =   $array[0];
			if(preg_match('/^\[([a-z_]+)\]$/',$href,$matches)){
				$val[]  =   $data2[$matches[1]];
			}else{
				$show   =   isset($array[1])?$array[1]:$value;
				// 替换系统特殊字符串
				$href   =   str_replace(
				array('[DELETE]','[EDIT]','[SEE]','[LIST]'),
				array('del?ids=[id]&model=[model_id]',
                    'edit?id=[id]&model=[model_id]&cate_id=[category_id]', 
				get_index_url().'/index.php?s=/home/article/detail/id/[id].html',
                    'index?pid=[id]&model=[model_id]&cate_id=[category_id]'),
				$href);

				// 替换数据变量
				$href	=	preg_replace_callback('/\[([a-z_]+)\]/', function($match) use($data){return $data[$match[1]];}, $href);

				$val[]	=	'<a href="'.get_nav_url($href).'" target="'.get_target_attr($href).'">'.$show.'</a>';
			}
		}
		$value  =   implode(' ',$val);
	}
	return $value;
}
//解析是否新页面打开
function get_target_attr($url){
	switch ($url) {
		case 'http://' === substr($url, 0, 7):
			$target ='_blank';
			break;
		default:
			$target ='_self';
			break;
	}
	return $target;
}
// 获取模型名称
function get_model_by_id($id){
	return $model = M('Model')->getFieldById($id,'title');
}

// 获取属性类型信息
function get_attribute_type($type=''){
	// TODO 可以加入系统配置
	static $_type = array(
        'num'       =>  array('数字','int(10) UNSIGNED NOT NULL DEFAULT 0'),
        'string'    =>  array('字符串','varchar(255) NOT NULL DEFAULT \'\''),
		'price'		=>	array('价格','decimal(10,2) NOT NULL DEFAULT 0'),
        'textarea'  =>  array('文本框','text NOT NULL'),
        'datetime'  =>  array('时间','int(10) NOT NULL DEFAULT 0'),
        'bool'      =>  array('布尔','tinyint(2) NOT NULL DEFAULT 0'),
        'select'    =>  array('枚举','char(50) NOT NULL DEFAULT \'\''),
    	'radio'		=>	array('单选','char(10) NOT NULL DEFAULT \'\''),
    	'checkbox'	=>	array('多选','varchar(100) NOT NULL DEFAULT \'\''),
    	'editor'    =>  array('编辑器','text NOT NULL'),
    	'picture'   =>  array('上传单图','int(10) UNSIGNED NOT NULL DEFAULT 0'),
    	'pictures'   =>  array('上传多图','varchar(255) NOT NULL DEFAULT \'\''),
    	'file'    	=>  array('上传附件','int(10) UNSIGNED NOT NULL DEFAULT 0'),
    	'maps'    	=>  array('地图地位','char(50) NOT NULL DEFAULT \'\''),
    	'address'    	=>  array('城市选择','char(20) NOT NULL DEFAULT \'\''),
    	'custommade'=>  array('自定义',''),
	//可扩展更多.....................................................
	);
	return $type?$_type[$type][0]:$_type;
}

/**
 * 获取对应状态的文字信息
 * @param int $status
 * @return string 状态文字 ，false 未获取到
 * @author huajie <banhuajie@163.com>
 */
function get_status_title($status = null){
	if(!isset($status)){
		return false;
	}
	switch ($status){
		case -1 : return    '已删除';   break;
		case 0  : return    '禁用';     break;
		case 1  : return    '正常';     break;
		case 2  : return    '待审核';   break;
		default : return    false;      break;
	}
}
function get_status_goods($status = null){
	if(!isset($status)){
		return false;
	}
	switch ($status){
		case 0  : return    '所有';     break;
		case 1  : return    '上架';     break;
		case 2  : return    '下架';   break;
		default : return    false;      break;
	}
}
// 获取数据的状态操作
function show_status_op($status) {
	switch ($status){
		case 0  : return    '启用';     break;
		case 1  : return    '禁用';     break;
		case 2  : return    '审核';		break;
		default : return    false;      break;
	}
}

/**
 * 获取文档的类型文字
 * @param string $type
 * @return string 状态文字 ，false 未获取到
 * @author huajie <banhuajie@163.com>
 */
function get_document_type($type = null){
	if(!isset($type)){
		return false;
	}
	switch ($type){
		case 1  : return    '目录'; break;
		case 2  : return    '主题'; break;
		case 3  : return    '段落'; break;
		default : return    false;  break;
	}
}

/**
 * 获取配置的类型
 * @param string $type 配置类型
 * @return string
 */
function get_config_type($type=0){
	$list = C('CONFIG_TYPE_LIST');
	return $list[$type];
}

/**
 * select返回的数组进行整数映射转换
 *
 * @param array $map  映射关系二维数组  array(
 *                                          '字段名1'=>array(映射关系数组),
 *                                          '字段名2'=>array(映射关系数组),
 *                                           ......
 *                                       )
 * @author 朱亚杰 <zhuyajie@topthink.net>
 * @return array
 *
 *  array(
 *      array('id'=>1,'title'=>'标题','status'=>'1','status_text'=>'正常')
 *      ....
 *  )
 *
 */
function int_to_string(&$data,$map=array('status'=>array(1=>'正常',-1=>'删除',0=>'禁用',2=>'未审核',3=>'草稿'))) {
	if($data === false || $data === null ){
		return $data;
	}
	$data = (array)$data;
	foreach ($data as $key => $row){
		foreach ($map as $col=>$pair){
			if(isset($row[$col]) && isset($pair[$row[$col]])){
				$data[$key][$col.'_text'] = $pair[$row[$col]];
			}
		}
	}
	return $data;
}

/**
 * 动态扩展左侧菜单,base.html里用到
 * @author 朱亚杰 <zhuyajie@topthink.net>
 */
function extra_menu($extra_menu,&$base_menu){
	foreach ($extra_menu as $key=>$group){
		if( isset($base_menu['child'][$key]) ){
			$base_menu['child'][$key] = array_merge( $base_menu['child'][$key], $group);
		}else{
			$base_menu['child'][$key] = $group;
		}
	}
}

/**
 * 获取参数的所有父级分类
 * @param int $cid 分类id
 * @return array 参数分类和父类的信息集合
 * @author huajie <banhuajie@163.com>
 */
function get_parent_category($cid){
	if(empty($cid)){
		return false;
	}
	$cates  =   M('Category')->where(array('status'=>1))->field('id,title,pid')->order('sort')->select();
	$child  =   get_category($cid);	//获取参数分类的信息
	$pid    =   $child['pid'];
	$temp   =   array();
	$res[]  =   $child;
	while(true){
		foreach ($cates as $key=>$cate){
			if($cate['id'] == $pid){
				$pid = $cate['pid'];
				array_unshift($res, $cate);	//将父分类插入到数组第一个元素前
			}
		}
		if($pid == 0){
			break;
		}
	}
	return $res;
}

/**
 * 检测验证码
 * @param  integer $id 验证码ID
 * @return boolean     检测结果
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function check_verify($code, $id = 1){
	$verify = new \Think\Verify();
	return $verify->check($code, $id);
}

/**
 * 获取当前分类的文档类型
 * @param int $id
 * @return array 文档类型数组
 * @author huajie <banhuajie@163.com>
 */
function get_type_bycate($id = null){
	if(empty($id)){
		return false;
	}
	$type_list  =   C('DOCUMENT_MODEL_TYPE');
	$model_type =   M('Category')->getFieldById($id, 'type');
	$model_type =   explode(',', $model_type);
	foreach ($type_list as $key=>$value){
		if(!in_array($key, $model_type)){
			unset($type_list[$key]);
		}
	}
	return $type_list;
}

/**
 * 获取当前文档的分类
 * @param int $id
 * @return array 文档类型数组
 * @author huajie <banhuajie@163.com>
 */
function get_cate($cate_id = null){
	if(empty($cate_id)){
		return false;
	}
	$cate   =   M('Category')->where('id='.$cate_id)->getField('title');
	return $cate;
}

// 分析枚举类型配置值 格式 a:名称1,b:名称2
function parse_config_attr($string) {
	$array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
	if(strpos($string,':')){
		$value  =   array();
		foreach ($array as $val) {
			list($k, $v) = explode(':', $val);
			$value[$k]   = $v;
		}
	}else{
		$value  =   $array;
	}
	return $value;
}

// 获取子文档数目
function get_subdocument_count($id=0){
	return  M('Document')->where('pid='.$id)->count();
}



// 分析枚举类型字段值 格式 a:名称1,b:名称2
// 暂时和 parse_config_attr功能相同
// 但请不要互相使用，后期会调整
function parse_field_attr($string) {
	if(0 === strpos($string,':')){
		// 采用函数定义
		return   eval(substr($string,1).';');
	}
	$array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
	if(strpos($string,':')){
		$value  =   array();
		foreach ($array as $val) {
			list($k, $v) = explode(':', $val);
			$value[$k]   = $v;
		}
	}else{
		$value  =   $array;
	}
	return $value;
}

/**
 * 获取行为数据
 * @param string $id 行为id
 * @param string $field 需要获取的字段
 * @author huajie <banhuajie@163.com>
 */
function get_action($id = null, $field = null){
	if(empty($id) && !is_numeric($id)){
		return false;
	}
	$list = S('action_list');
	if(empty($list[$id])){
		$map = array('status'=>array('gt', -1), 'id'=>$id);
		$list[$id] = M('Action')->where($map)->field(true)->find();
	}
	return empty($field) ? $list[$id] : $list[$id][$field];
}

/**
 * 根据条件字段获取数据
 * @param mixed $value 条件，可用常量或者数组
 * @param string $condition 条件字段
 * @param string $field 需要返回的字段，不传则返回整个数据
 * @author huajie <banhuajie@163.com>
 */
function get_document_field($value = null, $condition = 'id', $field = null){
	if(empty($value)){
		return false;
	}

	//拼接参数
	$map[$condition] = $value;
	$info = M('Model')->where($map);
	if(empty($field)){
		$info = $info->field(true)->find();
	}else{
		$info = $info->getField($field);
	}
	return $info;
}
/**
 * 获取行为类型
 * @param intger $type 类型
 * @param bool $all 是否返回全部类型
 * @author huajie <banhuajie@163.com>
 */
function get_action_type($type, $all = false){
	$list = array(
	1=>'系统',
	2=>'用户',
	);
	if($all){
		return $list;
	}
	return $list[$type];
}
function get_url_format($url)
{
	$urlParam = array('url'=>$url);
	$baseParam = array('model','controller','action');
	if ($url != '')
	{
		$urlArr = explode('/', trim($url,'/'));
		if (!empty($urlArr))
		{
			foreach ($urlArr as $k=>$v)
			{
				if ($k <= 2)
				{
					$urlParam[$baseParam[$k]] = $urlArr[$k];
				}
				else
				{
					if ($k % 2 == 1)
					{
						$urlParam[$urlArr[$k]] = rtrim($urlArr[$k+1],C('TMPL_TEMPLATE_SUFFIX'));
					}
				}
			}
		}
	}
	return $urlParam;
}
function safely_id($id)
{
	return md5(session_id().C('DATA_AUTH_KEY').$id);
}
function flist(){
	$map["pid"]=0;
	$map["status"]=1;
	$map["model"]=array('gt',3);
	$map["display"]=1;
	$list=M('category')->where($map)->select();
	return $list;
}
function get_Ordernum($status,$model){
	if($status){
		$map['status']=$status;
	}
	$map['id']=array('gt',0);
	$num=M($model)->where($map)->count();
	return $num?$num:0;
}
function get_status_title_bymodel($status = null,$model){
	if(!isset($status)){
		return false;
	}
	if($model=='exchange'){
		switch ($status){
			case 1  : return    '已提交';     break;
			case 2  : return    '已同意';   break;
			case 3  : return    '已拒绝';     break;
			case 4  : return    '换货中';     break;
			case 5  : return    '已完成';   break;
			default : return    false;      break;
		}
	}
	if($model=='backlist'){
		switch ($status){
			case 1  : return    '已提交';     break;
			case 2  : return    '已同意';   break;
			case 3  : return    '已拒绝';     break;
			case 4  : return    '退货中';     break;
			case 5  : return    '已完成';   break;
			default : return    false;      break;
		}
	}
	if($model=='cancel'){
		switch ($status){
			case 1  : return    '已提交';     break;
			case 2  : return    '已同意';   break;
			case 3  : return    '已拒绝';     break;
			default : return    false;      break;
		}
	}
	if($model=='order'){
		switch ($status){
			case -1  : return    '所有';     break;
			case 0  : return    '未支付';     break;
			case 1  : return    '已支付';     break;
//			case 2  : return    '已发货';   break;
//			case 3  : return    '已签收';     break;
			default : return    false;      break;
		}
	}
	if($model=='express'){
		switch ($status){

			case 1  : return    '未使用';     break;
			case 2  : return    '已使用';   break;

			default : return    false;      break;
		}
	}
	if($model=='records'){
		switch ($status){

			case 1  : return    '首页';     break;
			case 2  : return    '列表页';   break;
			case 3  : return    '内容页';     break;

			default : return    false;      break;
		}
	}

}