<?php
namespace app\branch\controller;
use app\index\model as model;
use think\Request;
class Vcode extends \app\common\controller\BranchBase
{

    //post 生成验证码
    public function save(){
        if(!$this->check('phone',['number','r','phone'])){
            return error(['desc'=>'请输入正确的手机号！']);
        }
        if(!$this->check('type',['number','r','min:0'])){
            return error($this->checkError());
        }
        //判断注册信息
        $login = model('branchLogin')->where('phone',$this->param('phone',''))->find();
        switch ($this->param('type','0')){
            case '113':
                if(!$login){
                    return error(['desc'=>'该手机号未注册']);
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
}