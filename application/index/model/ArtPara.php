<?php
namespace app\index\model;
use app\common\model;

class ArtPara extends model\Base{

    //在获取内容时，如果是
    public function img()
    {
        return $this->hasOne('\app\common\model\Image','id','img_id')->bind([
            'img_url'   => 'url'
        ]);
    }

    public function video(){
        return $this->hasOne('\app\common\model\Video','id','video_id')->bind([
            'video_url'   => 'url'
        ]);
    }

    public function art()
    {
        return $this->hasOne('art','id','main_id')->with(['user']);
    }
}