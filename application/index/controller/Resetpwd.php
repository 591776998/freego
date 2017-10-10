<?php
namespace app\index\controller;
use think\Request;
class Resetpwd extends \app\common\controller\Base
{

    public function save(){
        if(!$this->check('pwd',['string','r',32])){
            return error(['desc'=>'请输入新密码！']);
        }

        if(!$this->has('user_id')){
            $pre   = $this->param('pre','+86');
            $phone = $this->param('phone');
            //没有user_id
            if(!$this->check('phone', ['number','r','phone'])){
                return error(['desc'=>'请输入正确的手机号！']);
            }
            if(!$this->check('vcode', ['string','r'])){
                return error(['desc'=>'请输入验证码！']);
            }
            $now_user = model('UserLogin')->where('phone',$phone)->where('pre',$pre)->where('login_type',101)->find();
            if($now_user){

                $vcode = new \transfer\Vcode();
                $result_vcode = $vcode->contrast( $phone,$pre,102,$this->param('vcode'));
                if($this->isError($result_vcode)){
                    return error($result_vcode);
                }
            }else {
                return error(['desc' => '该手机号未注册！']);
            }
        }else{
            $user_id = $this->param('user_id',0);
            //有user_id
            $now_user = model('UserLogin')->where('id',$user_id)->where('token',$this->param['token'])->find();
            if($now_user){
                if(!$this->check('oldpwd',['string','r',32])){
                    return error(['desc'=>'请输入旧密码！']);
                }
                if($now_user['pwd'] != $this->param['oldpwd']){
                    return error(['desc'=>'旧密码错误！']);
                }
            }else{
                return error(['desc'=>'请重新登录！']);
            }
        }
        if($now_user['pwd'] == $this->param['pwd']){
            return success(model('User','service')->fullModel($now_user['id'],'0'));
        }
        return $this->_resetPwd($now_user['id'],$this->param['pwd']);
    }

    /****************************** 私有 *******************************************/
    protected function _resetPwd($id,$pwd){
        if(model('UserLogin')->where('id',$id)->data(['pwd'=>$pwd])->update()){
            return success(model('User','service')->fullModel($id,'0'));
        }else{
            return error(['desc'=>'修改密码失败！']);
        }
    }
}