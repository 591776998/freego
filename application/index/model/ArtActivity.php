<?php
namespace app\index\model;
use app\common\model;

class ArtActivity extends model\Base{

    public function leadHeadImg(){
        return $this->hasOne('\app\common\model\Image','id','lead_head_img_id')->bind(['lead_head_img_url'=>'url']);
    }

}