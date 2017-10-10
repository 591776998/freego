<?php
namespace app\index\controller;
use app\index\model as model;
use think\Request;
class Thirdsession extends \app\common\controller\Base
{
    //1.
    public function read(Request $request){

    }

    //1.新建会话 ，如果用户未绑定账号，就给空的用户模型
    public function save(){
        if(
            !$this->check('login_type',['number','r','min:0'])||
            !$this->check('only_sign',['string','r',32])
        ){
            return error($this->checkError());
        }
        if(!in_array($this->param('login_type'),[102,103])){
            return error(['desc'=>'暂时不支持该登录方式！']);
        }

        //1.查询该用户是否存在
        $res1 = model('UserLogin')->where('only_sign',$this->param('only_sign'))->where('login_type',$this->param('login_type'))->find();
        if($res1){
            //已经绑定了，这里直接正常登录
            $res2 = $this->login($this->param);
            if($this->isError($res2)){
                return error($res2);
            }else{
                return success($res2);
            }
        }else{
            //新用户,不新建用户
            return success(['user'=>(object)null,'token'=>'']);
        }

    }

    //登录
    protected function login($data = []){
        $result = model('User','service')->login($data['login_type'],$data);
        if($this->isError($result)){
            return ['code'=>'201','desc'=>'error','data'=>$result['data']];
        }
        $user_model = model('User','service')->fullModel($result['data']['id']);
        if(!$user_model){
            return ['code'=>'201','desc'=>'没找到该用户','data'=>$user_model['data']];
        }

        //查询普通登录方式 todo 这里返回密码和账号是因为前端要进行隐式登录，这里比较不合理
        $login_type = model('UserLogin')->where('id',$user_model['id'])->where('login_type',101)->find();
        if($login_type){
            $user_model['login_phone'] = $login_type['phone'];
            $user_model['pwd']   = $login_type['pwd'];
        }

        $user_model['token'] = $result['data']['token'];
        return ['code'=>'200','desc'=>'success','data'=>['user'=>$user_model,'token'=>$result['data']['token']]];
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