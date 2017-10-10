<?php
namespace app\index\model;
use app\common\model;

class ArtRubCar extends model\Base{

    public function artJoin(){
        return $this->hasOne('ArtJoin','id','join_id');
    }

    // 定义关联方法
    public function art()
    {                          //被关联的表 当前表的关联字段 被关联的表的字段
        return $this->belongsTo('Art','art_id','id',[],'LEFT');
    }

}