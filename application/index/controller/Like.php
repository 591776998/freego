<?php
namespace app\index\controller;
use think\Request;
class Like extends \app\common\controller\Base
{

    //点赞
    public function save(){

        if(!$this->check('target_id',['number','r','min:0'])||
            !$this->check('like_type',['number','r','min:0']))
        {
            return error($this->checkError());
        }

        if(!$this->user){
            return error(['desc'=> '请先登录,然后再点赞！'],'请先登录,然后再点赞！','401');
        }

        $result = model('Like','service')->add($this->user,$this->param('like_type',103),$this->param('target_id'));
        if($this->isError($result)){
            return error($result);
        }else{
            //返回一个点赞的对象给前端，并返回当前点赞总数？
            return success($result);
        }
    }

    //取消点赞
    public function delete(){
        if(!$this->check('id',['number','r','min:0']))
        {
            return error($this->checkError());
        }

        if(!$this->user){
            return error(['desc'=> '请先登录,然后取消点赞！'],'请先登录,然后取消点赞藏！','401');
        }
        $result = model('like','service')->delete($this->user,$this->param('id'));
        if($this->isError($result)){
            return error($result);
        }
        return success($result);
    }
}