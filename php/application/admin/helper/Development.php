<?php
/**
 * Helper层代码 开发帮助
 * @author 王远庆 <[562909771@qq.com]>
 */

namespace app\admin\helper;

use app\common\helper\Base;
use think\facade\Lang;

class Development extends Base
{
	private $dataValidate 		= null;
    private $mainTable          = '';

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

    private function autoMakeModelFile($parame){
        $tabList    = db('demo')->query('SHOW TABLE STATUS');

        $prefix     = config('database.prefix');
        $prefix     = empty($prefix) ? '' : $prefix;

        $modelNameFile 		= [];

        if (!empty($tabList)) {
            foreach ($tabList as $key => $value) {
                //去除表前缀
                $tname     = str_replace($prefix,'',$value['Name']);

                //一下划线分割
                $tnameArr   = explode('_',$tname);

                $modelName  = '';
                if (!empty($tnameArr)) {
                    foreach ($tnameArr as $v) {
                        $modelName .= ucwords($v);
                    }
                }

                if (!empty($modelName)) {
                    //检测文件是否存在
                    $file       = \Env::get('APP_PATH') .'api/model/'. $modelName .'.php';
                    $base       = \Env::get('APP_PATH') .'api/model/Demo.php';
                    if (!file_exists($file) && file_exists($base)) {

                        file_put_contents($file,str_replace('Demo', $modelName, file_get_contents($base)));
                        $modelNameFile[$modelName] = $modelName .'.php';
                    }
                }
            }
        }

        if (!empty($modelNameFile)) {

        	ksort($modelNameFile);

     		return ['Code' => '000000', 'Msg'=>lang('200000001',[count($modelNameFile),implode($modelNameFile,',')])];
        }

        return ['Code' => '000000', 'Msg'=>lang('200000002',[0])];
    }

    private function autoUpdateApiCode($parame){
    	//加载对应的语言包 没有自动创建
		$commonLangFile = \Env::get('APP_PATH') . 'common/lang/zh-cn/100000_common.php';

        $modelLangFile  = '';
        $allFile        = glob ( \Env::get('APP_PATH') . 'common/lang/zh-cn/' . '*' );

        if (!empty($allFile)) {

            foreach ($allFile as $file) {

                $prg    = '_'.strtolower($parame['modelName']).'.php';

                if (strpos($file,$prg) != false) {

                    $modelLangFile      = $file;
                }
            }
        }

		if (empty($modelLangFile) || !file_exists($commonLangFile) || !file_exists($modelLangFile)) {

			return ['Code' => '200000004', 'Msg'=>lang('200000004')];
		}

		$commonLang 	= include $commonLangFile;
		$modelLang 		= include $modelLangFile;

		$modelLang		= !empty($modelLang) ? $modelLang : [];

		$lang 			= /*array_change_key_case($commonLang) + */array_change_key_case($modelLang);

		return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>['lang'=>json_encode($lang)]];
    }
}
