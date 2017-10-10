<?php
namespace app\common\model;
use app\common\model;
class Order extends model\Base{

    public function orderItem(){
        return $this->hasMany('OrderItem','order_id','id');
    }

    //对应地点
    public function sellerPlace(){
        return $this->hasOne('place','id','seller_id')->with('seller');
    }

    //对应卖家
    public function sellerUser(){
        return $this->hasOne('user','id','seller_id');
    }

    //对应商户
    public function sellerSeller(){
        return $this->hasOne('seller','id','seller_id');
    }

    public function user(){
        return $this->hasOne('User','id','user_id')->with(['headImg']);
    }
}