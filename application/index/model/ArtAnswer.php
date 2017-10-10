<?php
namespace app\index\model;
use app\common\model;

class ArtAnswer extends model\Base{



    // 定义关联方法
    public function art()
    {
        //belongsTo($model, $foreignKey = '', $otherKey = '', $alias = [], $joinType = 'INNER')
        return $this->belongsTo('Art','main_id','id',[],'LEFT');
    }
}