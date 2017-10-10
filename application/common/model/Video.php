<?php
namespace app\common\model;

class Video extends \app\common\model\Base{

    public function getUrlAttr($value,$data){
        return video_show($value);
    }

    public function img(){
        return $this->hasOne('\app\common\model\Image','id','img_id')->bind([
            'img_url'   => 'url'
        ]);
    }
}