<?php
namespace app\index\model;
use app\common\model;

class Plan extends model\Base{

    //在获取内容时，如果是
    public function img()
    {
        return $this->hasOne('\app\common\model\Image','id','img_id')->bind([
            'img_url'   => 'url'
        ]);
    }



}