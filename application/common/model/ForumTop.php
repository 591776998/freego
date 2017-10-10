<?php
namespace app\common\model;
use app\common\model;

class ForumTop extends model\Base
{

    public function art(){
        return $this->hasOne('Art','id','art_id');
    }

}