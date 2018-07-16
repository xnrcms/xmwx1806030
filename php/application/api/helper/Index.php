<?php
/**
 * Helper层代码 接口聚合
 * @author 王远庆 <[562909771@qq.com]>
 */

namespace app\api\helper;

use app\common\helper\Base;
use think\facade\Lang;

class Index extends Base
{
	private $dataValidate 		= null;

	public function __construct($parame=[],$className='',$methodName='',$modelName='')
    {
        parent::__construct($parame,$className,$methodName,$modelName);
        $this->apidoc           = request()->param('apidoc',0);
    }
    
	/**
     * 初始化接口 固定不用动
     * @param  [array]  $parame     接口需要的参数
     * @param  [string] $className  类名
     * @param  [string] $methodName 方法名
     * @return [array]              接口输出数据
     */
    public function apiRun()
    {   
        if (!$this->checkData($this->postData)) return json($this->getReturnData());
        //加载验证器
        $this->dataValidate = new \app\api\validate\DataValidate;
        
        //规避没有设置主表名称
        if (empty($this->mainTable)) return $this->returnData(['Code' => '120020', 'Msg'=>lang('120020')]);
        
        //接口执行分发
        $methodName     = $this->actionName;
        $data           = $this->$methodName($this->postData);
        //设置返回数据
        $this->setReturnData($data);
        //接口数据返回
        return json($this->getReturnData());
    }

    private function index($parame) {

        //定义可以被访问的模块
    	$allowModel 	= ['api','admin'];

        //获取多接口数据，并解析
    	$apiData      	= request()->param('apiData');
        $apiData        = json_decode($apiData,true);

        $api 			= [];

        if (!empty($apiData)) {
            
            foreach ($apiData as $key => $value) {

            	if (!isset($value['apiParame']) || empty($value['apiParame'])) {
            		
            		return ['Code' => '300006', 'Msg'=>lang('300006',[$value['apiName']])];
            	}

            	//接口参数
            	$apiParame  = $value['apiParame'];

            	//解析接口名称，接口名称格式必须是 模块_控制器_方法 的格式
    			$info 		= explode('/',$value['apiName']);

    			//获取 模块名称，控制器名称，方法名称
				$mName 	= strtolower($info[0]);
				$cName 	= $info[1];
				$aName 	= $info[2];

                //判断接口模块是否在允许模块中，没有直接禁止请求
				if (!in_array(strtolower($mName),$allowModel) ) {

					return ['Code' => '300004', 'Msg'=>lang('300004')];
				}

				//检查控制器是否分存在，不存在禁止请求
				$file       = \Env::get('APP_PATH') . $mName . '/controller/'. $cName .'.php';

				if (!file_exists($file)) {

					return ['Code' => '300005', 'Msg'=>lang('300005',[$value['apiName']])];
				}

				 //定义对应的命名空间以及执行的方法
                $namespace 								= '\app\\'.$mName.'\controller\\' . $cName;
                $api[md5($namespace.$aName)]			= ['namespace'=>$namespace,'method'=>$aName,'apiName'=>$value['apiName'],'data'=>$apiParame];
            }

        }else{

        	return ['Code' => '300003', 'Msg'=>lang('300003'),'Data'=>$apiData];
        }

        if (empty($api)) {

			return ['Code' => '300007', 'Msg'=>lang('300007'),'Data'=>$api];
        }

        $backData  = [];

        //开始执行接口方法
		foreach ($api as $k => $v) {

			$namespace 				= $v['namespace'];
			$method 				= $v['method'];
            $apiName                = $v['apiName'];

			//设置接口参数
			$parameData 			    = array_merge($v['data'],['time'=>$parame['time'],'apiId'=>$parame['apiId']]);
			
			$apiObject 	                = new $namespace;
			$apiObject->apiMethod	    = $method;

            //获取接口数据
			$res                        = $apiObject->$method($parameData);

            //收集接口数据
            $data                       = $res->getData();

            $data['ApiUrl']             = url($apiName);
            
            $backData[$apiName]    		= $data;
		}

        return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$backData];
    }
}
