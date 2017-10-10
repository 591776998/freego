<?php
namespace app\index\controller;
use think\Request;
class Services extends \app\common\controller\Base
{
    //服务列表
    public function index(){
        if(
        !$this->check('page',['number','min:0'])||
        !$this->check('page_size',['number','min:0'])||
        !$this->check('place_id',['number','min:0'])||
        !$this->check('is_collect',['number','min:0'])
        ){
            return error($this->checkError());
        }
        $data = model('services','service')->index($this->user,$this->param('place_id',0),$this->param('is_collect',0),$this->param('page',1),$this->param('page_size',10));
        return success($data);
    }

    //服务详情
    public function read(){
        if(!$this->check('id',['number','r','min:0']))
        {
            return error($this->checkError());
        }

        $data = model('services','service')->read($this->user,$this->param('id'));
        if($this->isError($data)){
            return error($data);
        }else{
            return success($data);
        }

    }
}