<?php
namespace app\index\model;
use app\common\model;

class ArtCompanion extends model\Base{



    // 定义关联方法
    public function companion()
    {
        //belongsTo($model, $foreignKey = '', $otherKey = '', $alias = [], $joinType = 'INNER')
        return $this->belongsTo('Art','main_id','id',[],'LEFT');
    }
}