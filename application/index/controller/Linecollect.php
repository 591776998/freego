<?php
namespace app\index\controller;
class Linecollect extends \app\common\controller\Base
{

    public function index(){
        if($this->user){
            return success(model('MainNav','service')->read($this->user['id']));
        }else{
            return success([]);
        }
    }

}