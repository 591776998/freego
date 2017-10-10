<?php
namespace app\index\model;
use app\common\model;

class CommentImg extends model\Base{

    public function img(){
        return $this->hasOne('\app\common\model\Image','id','img_id')->bind(['img_url'=>'url']);
    }

}