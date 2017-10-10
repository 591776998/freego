<?php
namespace app\seller\model;
use app\common\model;

class Services extends model\Base{



    public function plan()
    {
        return $this->hasMany('Plan','services_id','id');
    }
    //在获取内容时，如果是
    public function img()
    {
        return $this->hasOne('\app\common\model\Image','id','img_id')->bind([
            'img_url'   => 'url'
        ]);
    }

}