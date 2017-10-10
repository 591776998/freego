<?php
namespace app\common\model;
use app\common\model;

class BranchLogin extends model\Base{


    public function login()
    {
        return $this->hasOne('BranchLogin','id','id');
    }


}