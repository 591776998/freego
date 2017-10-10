<?php
namespace app\index\controller;
use think\Request;
class Car extends \app\common\controller\Base
{
    //
    public function index(){
        return success(model('car','service')->index());
    }


}