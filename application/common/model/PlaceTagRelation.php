<?php
namespace app\common\model;
use app\common\model;

class PlaceTagRelation extends model\Base{


    public function tag(){
        return $this->hasOne('artTag','id','tag_id');
    }

}