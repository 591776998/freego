<?php
namespace app\common\model;
use app\common\model;

class Like extends model\Base{

    public function user(){
        return $this->hasOne('User','id','user_id')->bind([
            'user_nick_name'    => 'nick_name'
        ]);
    }


}