<?php
/**
 * Helper层代码示例
 * @author 王远庆 <[562909771@qq.com]>
 */

namespace app\api\helper;

use app\common\helper\Base;
use think\facade\Lang;

class Demo extends Base
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

    private function demo($parame) {

		$dbModel	= model('demo');

		/*$addData 	= $dbModel->addData(['text'=>'2222222222','name2'=>'sssssssssss','password'=>'wangyuanqing']);
		print_r($addData);exit;*/
		
		/*$delData 	= $dbModel->delData(1);
		print_r($delData);exit;*/

		//$upData = $dbModel->updateById(1,['text'=>'aaaaaa']);

		//print_r([$upData->getAttr('status'),$upData->getAttr('status_text')]);exit;

		$infoData = $dbModel->getOneById(1);print_r($infoData);exit;
		//
		
		//定义数据模型参数

		//主表名称，可以为空，默认当前模型名称
		$modelParame['MainTab']		= 'demo';

		//主表名称，可以为空，默认为main
		$modelParame['MainAlias']	= 'main';

		//主表待查询字段，可以为空，默认全字段
		$modelParame['MainField']	= [];

		//定义关联查询表信息，默认是空数组，为空时为单表查询,格式必须为一下格式
		//Rtype :`INNER`、`LEFT`、`RIGHT`、`FULL`，不区分大小写，默认为`INNER`。
		$RelationTab				= [];
		//$RelationTab['member']		= array('Ralias'=>'me','Ron'=>'me.uid=main.uid','Rtype'=>'LEFT','Rfield'=>array('nickname'));

		$modelParame['RelationTab']	= $RelationTab;

		//接口数据
		$modelParame['apiParame']	= $parame;
		
		//检索条件 需要对应的模型里面定义查询条件 格式为formatWhere...
		$modelParame['whereFun']	= 'formatWhereDemo';

		//排序定义
		$modelParame['order']		= 'main.sort desc';		
		
		//数据分页步长定义
		$modelParame['limit']		= $this->apidoc == 2 ? 1 : 10;

		//数据分页页数定义
		$modelParame['page']		= 1;

		//数据缓存是时间，默认0 不缓存 ,单位秒
		$modelParame['cacheTime']	= 0;

		//列表数据
		$lists 						= $dbModel->getPageList($modelParame);

		print_r([$lists,2]);exit;

    	$data = [
            'username'  => 'ssss',
            'age' => 'thinkphpqq.com',
        ];
    	if (!$this->dataValidate->scene('edit2')->check($data)) {
            dump($this->dataValidate->getError());exit;
        }
    	return ['Code' => '000000', 'Msg'=>lang('000000')];
    }
}
