<?php
namespace app\main\controller;
class Session extends \app\common\controller\MainBase
{

    public function save(){
        if(!$this->check('login_type',['number','r','min:0'])){
            return error($this->checkError());
        }

        if(101 == $this->param('login_type','0')){
            if(!$this->check('phone',['number','r','phone'])){
                return error(['desc'=>'请输入正确的手机号']);
            }
            if(!$this->check('pwd',['string','r',32])){
                return error($this->checkError());
            }
        }else{
            return error(['desc'=>'错误的登录方式！']);
        }

        $user_service = new \app\index\service\User();
        $result = $user_service->loginCheck($this->param('login_type'),$this->param);
        if($this->isError($result)){
            return error(['desc'=>'手机号或密码错误！']);
        }

        $user_model = $user_service->fullModel($result['data']['id']);
        if(!$user_model){
            return error(['desc'=>'没找到该用户']);
        }

        session(config('main_user_key'),$user_model);
        return success($user_model);

    }


    public function login(){

        return $this->fetch();
    }

}