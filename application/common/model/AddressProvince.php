<?php
namespace app\common\model;
use app\common\model;

class AddressProvince extends model\Base
{
    public function img()
    {
        return $this->hasOne('\app\common\model\Image','id','img_id')->bind([
            'img_url'   => 'url'
        ]);
    }

    public function sub(){
        return $this->hasMany('AddressCity','p_code','p_code');
    }

    public function father(){
        return $this->hasOne('AddressCountry','s_code','s_code');
    }
}