<?php
namespace app\index\model;
use app\common\model;


class CarModel extends model\Base
{

    public function parent(){
        return $this->hasOne('carType','id','car_type_id');
    }

}