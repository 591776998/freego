<?php
namespace app\index\model;
use app\common\model;

class ArtJoin extends model\Base{

    // 定义关联方法
    public function art()
    {                          //被关联的表 当前表的关联字段 被关联的表的字段
        return $this->belongsTo('Art','main_id','id',[],'LEFT');
    }

    //用户
    public function user(){
        return $this->hasOne('User','id','user_id')->with(['headImg','honour'])->withCount(['commentMain']);
    }

    public function carmodel(){
        return $this->hasOne('CarModel','id','car_model_id')->bind([
            'pre_name'   => 'title'
        ]);
    }
}