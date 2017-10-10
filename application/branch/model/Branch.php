<?php
namespace app\branch\model;
use app\common\model;

class Branch extends model\Base{


    public function login()
    {
        return $this->hasOne('BranchLogin','id','id');
    }

    public function sub(){
        return $this->hasMany('Branch','p_id','id');
    }
}