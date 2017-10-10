<?php
namespace app\index\controller;
use think\Request;
class Friend extends \app\common\controller\Base
{

    //加好友
    public function save( ){
        if(!$this->check('target_id',['number','r','min:0'])){
            return error($this->checkError());
        }

        if(!$this->user){
            return error(['desc'=> '请先登录,然后关注！'],'请先登录,然后关注！','401');
        }

        $result = model('Follow','service')->addFriendRequest( $this->user, $this->param('target_id'));
        if($this->isError($result)){
            return error($result);
        }
        return success($result);
    }

    //删除好友
    public function delete(){
        if(!$this->check('id',['number','r','min:0'])){
            return error($this->checkError());
        }

        if(!$this->user){
            return error(['desc'=> '请先登录,然后删除好友！'],'请先登录,然后删除好友！','401');
        }

        $result = model('Follow','service')->deleteEachOther($this->user['id'], $this->param('id','0'));
        if($this->isError($result)){
            return error($result);
        }
        return success($result);
    }

}