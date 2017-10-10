<?php
namespace app\common\model;
use app\common\model;

class Seller extends model\Base
{

    public function img(){
        return $this->hasOne('\app\common\model\Image','id','img_id')->bind(['img_url'=>'url']);
    }

    public function place(){
        return $this->hasOne('place','seller_id','id');
    }

    public function info()
    {
        return $this->hasOne('SellerInfo','seller_id','id');
    }
}
