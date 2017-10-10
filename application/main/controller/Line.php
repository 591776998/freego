<?php
namespace app\main\controller;
class Line extends \app\common\controller\MainBase
{

    public function all(){
//        $service = new \app\index\service\Place();
//        $data = $service->index($this->user,'',98);
//        $this->assign('data',$data);
        return $this->fetch();
    }

}