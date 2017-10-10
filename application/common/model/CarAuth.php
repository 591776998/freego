<?php
namespace app\common\model;
use app\common\model;


class CarAuth extends model\Base
{
    public function carmodel()
    {
        return $this->hasOne('CarModel','id','car_model_id')->bind([
            'pre_name'   => 'title'
        ]);
    }
}