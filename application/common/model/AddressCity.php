<?php
namespace app\common\model;
use app\common\model;

class AddressCity extends model\Base
{
    public function img()
    {
        return $this->hasOne('\app\common\model\Image','id','img_id')->bind([
            'img_url'   => 'url'
        ]);
    }

    public function sub(){
        return $this->hasMany('AddressTown','c_code','c_code');
    }


    public function father(){
        return $this->hasOne('AddressProvince','p_code','p_code');
    }
}