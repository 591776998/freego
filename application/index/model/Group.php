<?php
namespace app\index\model;
use app\common\model;

class Group extends model\Base
{

    //获取子集
    public function img(){
        return $this->hasOne('\app\common\model\Image','id','img_id')->bind([
            'img_url'   => 'url'
        ]);
    }
}