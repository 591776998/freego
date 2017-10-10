<?php
namespace app\index\model;
use app\common\model;
class Order extends model\Base{

    public function orderItem(){
        return $this->hasMany('OrderItem','order_id','id');
    }

}