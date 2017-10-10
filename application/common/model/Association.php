<?php
namespace app\common\model;
use app\common\model;

class Association extends model\Base{

    //
    public function sub(){
        return $this->hasMany('Association','p_id','id')->order('order_weight','desc')->order('id','desc');
    }
}