<?php
namespace app\index\controller;
use think\Request;
class Applyfriend extends \app\common\controller\Base
{

    //好友申请列表
    public function index(){

    }

    //发一个好友申请
    public function save(){
        if(!$this->check('target_id',['number','r','min:0'])){
            return error($this->checkError());
        }
        
        if(!$this->user){
            return error(['desc'=>'请登录再申请好友'],'请登录再申请好友','401');
        }

        $res = model('Follow','service')->addFriendRequest($this->user['id'], $this->param('target_id'),$this->param('content',''));
        if($this->isError($res)){
            return error($res);
        }

        $content = $this->param('content','请求成为您的好友')?$this->param('content','请求成为您的好友'):'请求成为您的好友';
        //生成请求消息并推送
        $msg = model('msg','service');
        $msg->type(102,101)
            ->sender($this->user['id'],101)
            ->target($this->param('target_id'),101)
            ->content($this->param('content',''),'请求成为您的好友')
            ->action(101,($res['data']['id']?$res['data']['id']:0))
            ->send()
            ->push($content,'好友请求');

        return success($res['data']);
    }

    //同意或拒绝好友请求
    public function update(){
        if(
            !$this->check('action_id',['number','r','min:0'])||
            !$this->check('action',['number','r','min:0'])
        ){
            return error($this->checkError());
        }

        if(!$this->user){
            //未登录的
            return error(['desc'=>'请登录再申请好友'],'请登录再申请好友','401');
        }

        $req = model('UserFollowRequest')->where('id',$this->param('action_id'))->find();
        if(!$req){
            return error(['desc'=>'找不到该请求！']);
        }

        $res = model('Follow','service')->updateRequest($this->user['id'], $req['user_id'],$this->param('action'));
        if($this->isError($res)){
            return error($res);
        }

        //设置消息为已同意或拒绝
        model('MsgSession')->where('session_type',102)->where('session_sub_type',101)->where('action_id',$this->param('action_id'))->where('action_status','101')->update([
            'action_status'=>(101 == $this->param('action')?'102':'103')
        ]);



        //根据 同意还是拒绝来 发推送
        if(101 == $this->param('action')){
            $msg_title = '同意了您的好友请求';
        }else{
            $msg_title = '拒绝了您的好友请求';
        }
        model('msg','service')
            ->type(101,100)
            ->sender($this->user['id'],101)
            ->target($req['user_id'],101)
            ->content('',$msg_title)
            ->action(0,$this->user['id'])
            ->send()
            ->push($msg_title,'好友申请结果');

        return success((object)null);
    }

}