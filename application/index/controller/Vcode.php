<?php
namespace app\index\controller;
use app\index\model as model;
use think\Request;
class Vcode extends \app\common\controller\Base{

    //验证码
    //一个restful风格的控制器

    //get list
    public function index(){

    }

    //get 需要id
    public function read(){

    }

    //post 生成验证码
    public function save(){
        if(!$this->check('phone',['number','r','phone'])){
            return error(['desc'=>'请输入正确的手机号！']);
        }
        if(!$this->check('code_type',['number','r','min:0'])){
            return error($this->checkError());
        }

        $is_register = model('User','service')->isRegister($this->param('phone'));
        switch ($this->param('code_type')){
            case '101':
                if($is_register){
                    return error(['desc'=>'该手机号已经注册']);
                }
                break;
            case '102':
                if(!$is_register){
                    return error(['desc'=>'该手机号还未注册']);
                }
                break;
            case '103':
                if($is_register){
                    return error(['desc'=>'该手机号已经注册']);
                }
                break;
        }

        $vcode = new \transfer\Vcode();
        $result = $vcode->send( $this->param('phone'),$this->param('pre','+86'),$this->param('code_type'));
        if($this->isError($result)){
            return error($result);
        }else{
            return success($result);
        }
    }

    //put 需要id
    public function update(){


    }

    //delete 需要id
    public function delete(){


    }
}