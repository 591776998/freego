<?php
namespace app\common\model;
use app\common\model;

class Chanel extends model\Base{

    //图片获取

    //关联获取图片
    public function img()
    {
        return $this->hasOne('\app\common\model\Image','id','img_id')->bind([
            'img_url'   => 'url'
        ]);
    }

    //跳转控制
    public function jump(){
        return $this->hasOne('\app\index\model\Jump','id','jump_id');
    }

}