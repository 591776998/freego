<?php
namespace app\common\model;
use app\common\model;


class CarBrand extends model\Base
{
    //返回全部的品牌和所属的车系，车型
    public function sub(){
        return $this->hasMany('CarType','car_brand_id','id')->order('order_weight','desc')->with(['sub']);
    }

    public function img()
    {
        return $this->hasOne('\app\common\model\Image','id','img_id')->bind([
            'img_url'   => 'url'
        ]);
    }
}