<?php
namespace Api\Controller;
use Think\Controller;
/**
 * Common控制器
 * @author 王远庆
 */
class CommonController extends Controller {
	protected $ApiParam = array();
	protected function _initialize(){
		header('Access-Control-Allow-Origin:*');
		/* 读取数据库中的配置 */
		$config 	= S('DB_CONFIG_DATA');
		if(!$config){
			$config = api('Config/lists');
			S('DB_CONFIG_DATA',$config);
		}
		C($config); //添加配置
		//初始化语言包
		$this->Lang		= L('API_LANG');
	}
	/**
	 * 返回JSON数据
	 */
	protected function ReturnJson($data=array()){
		$base		= array('Code' => '100000', 'Msg'=>$this->Lang['100000'], 'Time'=>date('Y-m-d H:i:s',NOW_TIME),'ApiUrl'=>U(),'Data'=>(object)array());
		$BackData	= array_merge($base,$data);
		foreach ($BackData as $k=>$v){
			if ($k == 'Code'){
				$BackData[$k]	= $v*1;
			}
		}
		$jsondata	= json_encode($BackData);
		$IsLogs		= C('IS_API_LOGS');
		if ($IsLogs == true){
			//记录接口调用日志
			$Logs['api_url']		= U();
			$Logs['create_time'] 	= NOW_TIME;
			$Logs['ip']				= getip();
			$Logs['msg']			= $BackData['Msg'];
			$Logs['parames']		= json_encode($this->GetApiParam());
			M('api_logs')->add($Logs);
		}
		die($jsondata);
	}
	/**
	 *数据安全加密
	 */
	protected function ArraySort($data=array(),$hash){
		if (empty($data)){
			return md5(rand(1, 5).C('DATA_AUTH_KEY')) === $hash ? true : false;
		};
		$data		= array_flip($data);
		ksort($data);
		$data		= array_flip($data);
		$value='';
		foreach($data as $v){
			$value .= $v;
		}
		$this->ApiHash = md5($value.C('DATA_AUTH_KEY'));
		return $this->ApiHash === $hash ? true : false;
	}
	/**
	 * 数据安全校验
	 */
	protected function CheckData($PostData,$Fields){
		if (empty($Fields) || empty($PostData)) $this->ReturnJson();
		//获取接口信息
		if (I('get.getapiinfo') === '1') $this->ReturnJson(array('Code' => '0', 'Msg'=>'ok','Data'=>array($this->errorInfo,$Fields)));
		$sign		= array();
		$BackData 	= array();
		//先判断数据传递是否完整合法
		foreach ($Fields as $val){
			//不能为空
			if ($val[2] == 1){
				if (($val[1] == 'Int' && intval($PostData[$val[0]]) <= 0) || ($val[1] == 'String' && empty($PostData[$val[0]]))){
					$this->ReturnJson(array('Code' => $val[4], 'Msg'=>$this->Lang[$val[4]]));
				}
			}
			//hash不参与签名
			if ($val[0] != 'hash'){
				$sign[]	= $val[0];
			}
			else{
				$hash = $PostData[$val[0]];
			}
			$BackData[$val[0]]	= $val[1] == 'Int' ? intval($PostData[$val[0]]) : trim($PostData[$val[0]]);
		}
		//判断签名校验是否通过
		if ($this->ArraySort($sign,$hash) == false){
			$this->ReturnJson(array('Code' => '100003', 'Msg'=>$this->Lang['100003']));
		}
		return $this->SetApiParam($BackData);
	}

	//设置接口参数
	protected function SetApiParam($param){
		$this->ApiParam =  $param;
		return $this->ApiParam;
	}
	//获取接口参数
	protected function GetApiParam(){
		return $this->ApiParam;
	}

	/**
	 * 初始化接口调用
	 * @param String $parame 接口参数，必须输json格式的字符串
	 * @param String $model	 接口对用的模块
	 */
	protected function Helper($parame,$model){
		if (empty($parame)){
			$this->ReturnJson(array('Code' =>'100008','Msg'=>$this->Lang['100008']));
		}
		$class		= R('Api/'.$model.'/apiRun', array('parame'=>$parame),'Helper');
		if ($class === false){
			$this->ReturnJson(array('Code' =>'100007','Msg'=>$this->Lang['100007']));
		}
		return $class;
	}
}