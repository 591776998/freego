<?php
namespace app\common\model;
use app\common\model;

class ArtAnswer extends model\Base{



    // 定义关联方法
    public function art()
    {
        return $this->belongsTo('Art','main_id','id',[],'LEFT');
    }
}