<?php
namespace app\index\controller;
use think\Request;
class Association extends \app\common\controller\Base
{

    //1.协会列表
    public function index(){
        if(!$this->check('p_id',['number','min:0'])){
            return error($this->checkError());
        }

        //1.类型
        $service = model('Association','service');
        $result = $service->index($this->param('p_id',0));
        return success($result);
    }

    //新建
    public function save(){
        if(!$this->check('p_id',['number','r','min:0'])||
            !$this->check('title',['string',1])||
            !$this->check('order_weight',['number','min:0']))
        {
            return error($this->checkError());
        }

        $service = model('Association','service');
        $result = $service->add($this->param('p_id',0),$this->param('title'),$this->param('order_weight',100));
        if($this->isError($result)){
            return error($result);
        }
        return success($result);
    }

}