<?php
//api控制器父类
namespace app\api\controller;
use think\Controller;
class ApiBase extends Controller
{
    protected $param = [];//输入参数
    protected $request = null;
    protected $check   = null;

    public function _initialize()
    {
        //1.通过统一的参数判断
        $this->request = \think\Request::instance();
        $this->param = $this->request->param();
        $this->check = checkModel($this->param);

        $this->view->replace([
            '__PUBLIC__'    =>  config('static_path')
        ]);
        $this->assign('baseUrl',config('request_base_url'));
        $this->_init();
    }

    //留给子类重写的方法，用来做初始化的操作
    public function _init(){

    }

    //判断参数是否存在
    public function has($param){
        return $this->check->has($param);
    }

    //参数验证
    public function check($param, $rules = [], $param_name = false){
        return $this->check->index($param, $rules, $param_name);
    }

    //获取param参数
    public function param($param = '',$default = false){
        return $this->check->param($param, $default);
    }

    //设置输入参数
    public function setParam($data = []){
        return $this->check->data($data);
    }

    //获取参数验证的错误信息
    public function checkError(){
        return $this->check->getError();
    }

    //没有找到对应的操作
    public function _empty($name=''){
        return error( ['debug'=>'url_error:'.$name], '没找到请求的资源:'.$name, '404');
    }

    //判断服务层的返回
    public function isError( $data, $exit = false){
        if($data['code'] != '200'){
            return true;
        }
        return false;
    }


}
