<?php
namespace app\common\model;
use app\common\model;

class AssociationUser extends model\Base{

    //
    public function img(){
        return $this->hasOne('\app\common\model\Image','id','img_id')->bind(['img_url'=>'url']);
    }
}