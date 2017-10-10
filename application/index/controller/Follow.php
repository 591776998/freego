<?php
namespace app\index\controller;
use think\Request;
class Follow extends \app\common\controller\Base
{

    //关注
    public function save(){
        if(!$this->check('target_id',['number','r','min:0'])||
            !$this->check('token',['string',32])){
            return error($this->checkError());
        }

        if(!$this->user){
            return error(['desc'=> '请先登录,然后关注！'],'请先登录,然后关注！','401');
        }

        $result = model('Follow','service')->add( $this->user['id'], $this->param('target_id'));
        if($this->isError($result)){
            return error($result);
        }

        model('msg','service')
            ->type(101, 101)
            ->sender( $this->user['id'],101)
            ->target( $this->param('target_id'), 101)
            ->action(101, $this->user['id'])
            ->content('','开始关注您了哦')
            ->send()
            ->push('有用户关注了您','关注');

        return success($result);
    }

    public function delete(){
        if(!$this->check('target_id',['number','r','min:0'])){
            return error($this->checkError());
        }

        $result = model('Follow','service')->delete( $this->user['id'], $this->param('target_id'));
        if($this->isError($result)){
            return error($result);
        }

        return success($result);
    }


}