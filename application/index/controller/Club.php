<?php
namespace app\index\controller;
use think\Request;
class Club extends \app\common\controller\Base
{
    //
    public function index(){
        //输出俱乐部列表信息 todo
        return success(model('Club')->page($this->param('page',1),$this->param('page_size',10))->select());
    }


}