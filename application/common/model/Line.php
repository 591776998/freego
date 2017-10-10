<?php
namespace app\common\model;
use app\common\model;

class Line extends model\Base{

    //定位点
    public function point(){
        return $this->hasMany('LineItem','line_id','id')->order('order_weight','desc');
    }
}

