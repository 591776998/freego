<?php
namespace app\seller\model;
use app\common\model;

class Seller extends model\Base{


    public function login()
    {
        return $this->hasOne('SellerLogin','id','id');
    }

    public function info()
    {
        return $this->hasOne('SellerInfo','seller_id','id');
    }


}