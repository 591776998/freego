<?php
namespace app\common\model;
use app\common\model;

class Branch extends model\Base{
    protected $pk = 'id';


    public function login()
    {
        return $this->hasOne('BranchLogin','id','id');
    }

    public function sub(){
        return $this->hasMany('Branch','p_id','id');
    }
}