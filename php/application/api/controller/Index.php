<?php
/**
 * Controller层 多接口调用
 * @author 王远庆 <[email address]>
 */

namespace app\api\controller;

use app\common\controller\Base;

//接口默认不提供Index访问
class Index extends Base
{
    public      $apiHash        = '';
    public      $apiMethod      = '';
    protected   $apinum         = 300000;//接口编号

    public function index()
    {
        //接口动态参数定义，根据业务需求决定
        $checkData  = [
        ['isManyApi','Int',1,'300000','/','聚合标识','多个接口调用的参数标识'],
        ['apiData','Json',1,'300002','/','多接口数据','多个接口采用json格式传递'],

        //['字段标识','字段类型(Int,String,Json,Float,Bool)','是否必填','参数编号','默认值','字段名称','字段描述'],
        ];

        //接口调用成功需要返回的参数
        $backData   = [
        ['id','Int','ID','用户组ID'],

        //['字段标识','字段类型(Int,String,Json,Float,Bool)','字段名称','字段描述'],
        ];

        //接口文档数据
        $this->apidoc($checkData,$backData);
        //设置接口校验数据
        $this->setCheckData($checkData);
        //设置接口返回数据
        $this->setBackData($backData);

        //用户提交的数据
        $postData                               = request()->param();

        //数据校验
        $checkData                              = $this->checkData($postData);
        if (!$checkData) {

            return json($this->getReturnData());
        }

        //执行方法名 默认当前action
        $method                                 = request()->action();
        
        //定义类名
        $className                              = 'Index';
        //定义命名空间
        $namespace                              = '\app\api\helper';
        //数据传递
        $parame                                 = json_encode($checkData);
        //执行操作
        $this->helper($parame,$namespace,$className,$method);
        //数据返回
        return json($this->getReturnData());
    }

    public function area(){
        $page       = request()->param('page',1);

        echo "共24页，正在执行第".$page."页.....";

        $num        = request()->param('num',0);
        $arealist   = db('region')->where('lng','eq','')->limit(25)->page($page)->select();

        

        if (!empty($arealist)) {
            foreach ($arealist as $key => $value) {
                
                $updata                     = [];
                $updata['shortname']        = $value['shortname'];
                $updata['merger_name']      = $value['merger_name'];
                $updata['code']             = $value['code'];
                $updata['zip_code']         = $value['zip_code'];
                $updata['lng']              = $value['lng'];
                $updata['lat']              = $value['lat'];

                $map                        = [];
                $map['area']                = $value['name'];
                $map['level']               = $value['level'];

                $id                         = db('region')->where($map)->value('id');

                if (!empty($id) && $id > 0) {
                    //model('region')->updateById($id,$updata);
                }else{
                    $num ++;
                }
            }
            
            $page++;

            echo "<script>location.href='".url('area','page='.$page.'&num='.$num)."';</script>";
        }else{
            echo $num;exit;
        }
    }
}