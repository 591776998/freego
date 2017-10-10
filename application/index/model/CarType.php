<?php
namespace app\index\model;
use app\common\model;


class CarType extends model\Base
{
    //返回全部的品牌和所属的车系，车型
    public function sub(){
        return $this->hasMany('CarModel','car_type_id','id')->order('order_weight desc');
    }

    //返回父级
    public function parent(){
        return $this->hasOne('carBrand','id','car_brand_id');
    }
}