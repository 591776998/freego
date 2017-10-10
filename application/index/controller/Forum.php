<?php
namespace app\index\controller;
use think\Request;
class Forum extends \app\common\controller\Base
{

    public function read(){
        if(!$this->check('id',['number','r','min:0'])){
            return error($this->checkError());
        }
        $data = model('forum')->where('id',$this->param('id',0))->find();
        if(!$data){
            return error(['desc'=>'未找到该论坛']);
        }
        return success($data);
    }

    public function save( Request $request){

    }

    public function delete(Request $request){

    }

}