<?php
namespace app\index\model;
use app\common\model;

class ArtTagRelation extends model\Base{


    public function tag(){
        return $this->hasOne('artTag','id','tag_id');
    }

}