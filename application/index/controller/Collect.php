<?php
namespace app\index\controller;
use think\Request;
class Collect extends \app\common\controller\Base
{
    public function save(){
        if(!$this->check('type',['number','r','min:0'])||
            !$this->check('sub_type',['number','min:0'])||
            !$this->check('target_id',['number','r','min:0']))
        {
            return error($this->checkError());
        }

        if(!$this->user){
            return error(['desc'=> '请先登录,然后收藏！'],'请先登录,然后收藏！','401');
        }

        $result = model('Collect','service')->add($this->user,$this->param('type'),$this->param('sub_type','0'),$this->param('target_id'));
        if($this->isError($result)){
            return error($result);
        }
        return success($result);
    }

    public function delete(){
        if(!$this->check('id',['number','r','min:0']))
        {
            return error($this->checkError());
        }

        if(!$this->user){
            return error(['desc'=> '请先登录,然后取消收藏！'],'请先登录,然后取消收藏！','401');
        }
        $result = model('Collect','service')->delete($this->user,$this->param('id'));
        if($this->isError($result)){
            return error($result);
        }
        return success($result);
    }
}