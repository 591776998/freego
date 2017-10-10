<?php
namespace app\index\model;
use app\common\model;

class Seller extends model\Base
{

    public function img(){
        return $this->hasOne('\app\common\model\Image','id','img_id')->bind(['img_url'=>'url']);
    }

    public function place(){
        return $this->hasOne('place','seller_id','id')->with('img');
    }

    public function sellerinfo(){
        return $this->hasOne('place','seller_id','id');
    }
}
