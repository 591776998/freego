<?php
namespace app\index\controller;
class Pre extends \app\common\controller\MainBase
{
    //区号
    public function index(){
        return success(model('pre')->select());
    }


}