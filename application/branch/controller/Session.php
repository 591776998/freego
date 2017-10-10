<?php
namespace app\branch\controller;
class Session extends \app\common\controller\BranchBase
{

    //post 保存登录会话
    public function save(){
        if(!$this->check('type',['number','r','min:0'])){
            return error($this->checkError());
        }
        if(!$this->check('phone',['number','r','phone'])){
            return error(['desc'=>'请输入正确的手机号']);
        }

        //验证密码
        $login = model('BranchLogin')->where('phone',$this->param('phone',''))->find();
        if(!$login){
            return error(['desc'=>'手机号不存在']);
        }

        if(101 == $this->param('type','0')){
            if(!$this->check('pwd',['string','r',32])){
                return error($this->checkError());
            }
            if(strtoupper(trim($login['pwd'])) != strtoupper(trim($this->param('pwd'))) ){
                return error(['desc'=>'手机号或密码错误']);
            }

        }elseif(102 == $this->param('type',0)){
            //看验证码是否正确
            if(!$this->check('vcode',['string','r'])){
                return error(['desc'=>'请输入验证码']);
            }
            //验证验证码是否正确
            $result_vcode = model('Vcode','service')->contrastVcode( $this->param('phone'),$this->param('vcode'),113);
            if($this->isError($result_vcode)){
                return error($result_vcode);
            }
        }else{
            return error(['desc'=>'错误的登录方式']);
        }

        //通过，查询用户信息
        $user = model('Branch')->where('branch_id',$login['branch_id'])->find();
        if(!$user){
            return error(['desc'=>'未找到该代理商']);
        }


        $user['login'] = $login;

        //保存session
        session(config('admin_branch_key'),$user);


        return success(['role'=>$login['role']]);
    }

    public function login(){

        return $this->fetch();
    }
}