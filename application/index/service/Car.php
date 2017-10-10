<?php
namespace app\index\service;

class Car extends \app\common\service\Base
{

    //获取全部的车品牌->车系->车型 的列表
    public function index(){


        $data = model('carBrand')->with(['img','sub'])->select();
        return $this->success($data);
    }

}