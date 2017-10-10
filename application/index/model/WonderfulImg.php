<?php
namespace app\index\model;
use app\common\model;

class WonderfulImg extends model\Base{


    public function img()
    {
        return $this->hasOne('\app\common\model\Image','id','img_id')->bind([
            'img_url'   => 'url'
        ]);
    }

    public function user(){
        return $this->hasOne('User','id','user_id')->with(['headImg']);
    }

    public function like(){
        return $this->hasOne('Like','main_id','id')->where('type','120');
    }

    public function art(){
        return $this->hasOne('art','id','art_id');
    }
}