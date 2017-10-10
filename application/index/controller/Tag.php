<?php
namespace app\index\controller;
use think\Request;
class Tag extends \app\common\controller\Base
{

    //标签列表
    public function index( ){
        if(!$this->check('type',['number','min:0'])){
            return error($this->checkError());
        }
        if(!$this->check('sub_type',['number','min:0'])){
            return error($this->checkError());
        }

        $result = model('ArtTag','service')->index($this->param('type',0),$this->param('sub_type',0));
        if($this->isError($result)){
            return success([]);
        }
        return success($result);
    }


}