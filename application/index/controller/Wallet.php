<?php
namespace app\index\controller;
class Wallet extends \app\common\controller\Base
{

    public function read(){
        if(!$this->check('id',['number','r','min:0'])){
            return error(['desc'=>'错误的用户Id']);
        }

        if(!$this->user){
            return error(['desc'=>'请登录后继续操作！'],'请登录后继续操作！','401');
        }

        return success(['money'=>$this->user['money'],'point'=>$this->user['point']]);
    }


}