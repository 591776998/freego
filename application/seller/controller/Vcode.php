<?php
namespace app\seller\controller;
class Vcode extends \app\common\controller\SellerBase
{

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
        if(!$this->check('type',['number','r','min:0'])){
            return error($this->checkError());
        }
        //判断注册信息
        $login = model('sellerLogin')->where('phone',$this->param('phone',''))->find();
        switch ($this->param('type','0')){
            case '111':
                if(!$login){
                    return error(['desc'=>'该手机号未注册']);
                }
                break;
            case '112':
                if($login){
                    return error(['desc'=>'该手机号已注册']);
                }
                break;
            default:
                return error(['desc'=>'错误的验证码类型']);
        }

        $result = model('Vcode','service')->send($this->param('type'),$this->param('phone'));
        if($this->isError($result)){
            return error($result);
        }else{
            return success([]);
        }
    }

    //put 需要id
    public function update(){


    }

    //delete 需要id
    public function delete(){


    }
}