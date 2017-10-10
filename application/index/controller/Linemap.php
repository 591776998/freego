<?php
namespace app\index\controller;
use think\Request;
class Linemap extends \app\common\controller\Base
{
    //

    public function read( ){
        $result = model('line','service')->read($this->param('id'),101);
        if($this->isError($result)){
            return error($result);
        }else{
            return success($result);
        }
    }

}