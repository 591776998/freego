<?php
namespace app\index\model;
use app\common\model;

class Forum extends model\Base
{

    //获取子集
    public function sub(){
        return $this->hasMany('Forum','p_id','id')->order('order_weight desc')->order('id asc');
    }
}