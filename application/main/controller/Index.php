<?php
namespace app\main\controller;
class Index extends \app\common\controller\MainBase
{

    public function index(){
        if(!$this->user){
            return $this->errorLogin();
        }

        return $this->fetch();
    }
}