<?php
namespace app\common\service;

class Base
{
    public function __construct($data = null)
    {
        if(method_exists($this,'_init')){
            if(!is_null($data)){
                $this->_init($data);
            }else{
                $this->_init();
            }
        }
    }

    //返回一个逻辑层
    public function logic( $name = ''){
        return \think\Loader::model( $name, 'logic');
    }

    //1.向控制器返回错误
    public function error($data = array(), $desc = 'error', $code = '201'){
        if($desc == 'error'){
            if(isset($data['desc'])){
                $desc = $data['desc'];
            }else if(isset($data['debug']['desc'])){
                $desc = $data['debug']['desc'];
            }
        }
        return ['data'=>$data,'desc'=>$desc,'code'=>$code];
    }

    //向控制器返回成功
    public function success($data = array(), $desc = 'success', $code = '200'){
        return ['data'=>$data,'desc'=>$desc,'code'=>$code];
    }

    //判断返回的是否是错误
    public function isError( $data, $exit = false){
        if($data['code'] != '200'){
            return true;
        }
        return false;
    }
}