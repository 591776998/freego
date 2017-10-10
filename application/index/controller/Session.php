<?php
namespace app\index\controller;
use app\index\model as model;
use think\Request;
class Session extends \app\common\controller\Base
{
    //1.
    public function read(){

    }

    //post 保存登录会话
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

        $result = model('User','service')->login($this->param('login_type'),$this->param);
        if($this->isError($result)){
            return error($result);
        }

        $user_model = model('User','service')->fullModel($result['data']['id']);
        if(!$user_model){
            return error(['desc'=>'没找到该用户']);
        }

        $user_model['token'] = $result['data']['token'];
        return success(['token'=>$result['data']['token'],'user'=>$user_model]);
    }

    //注销登录
    public function delete(){
        if(!$this->check('id',['number','r','min:1'])){
            return error($this->checkError());
        }
        if(!$this->user){
            return error(['desc'=>'您尚未登录！']);
        }

        $result = model('User','service')->logout($this->user,$this->param('id'));
        if($this->isError($result)){
            return error($result);
        }

        return success(['user'=>(object)null,'token'=>'']);
    }
}