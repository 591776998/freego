<?php
namespace app\index\controller;
use think\Request;
class Comment extends \app\common\controller\Base
{
    //
    public function read(){
        //103文章 104服务
        if(!$this->check('id',['number','r','min:0'])){
            return error($this->checkError());
        }
        $result = model('Comment','service')->read($this->param('id'));
        if($this->isError($result)){
            return error($result);
        }
        return success($result);
    }

    //get list
    public function index(){
        //103文章 104服务
        if(!$this->check('type',['number','min:0'])||
            !$this->check('main_id',['number','min:0'])||
            !$this->check('user_id',['number','min:0'])||
            !$this->check('page',['number','min:1'])||
            !$this->check('page_size',['number','min:1']))
        {
            return error($this->checkError());
        }


        $data = model('comment','service')->index($this->user,$this->param('type',0),$this->param('main_id',0),$this->param('user_id',0),$this->param('page',1),$this->param('page_size',10));
        if($this->isError($data)){
            return error($data);
        }
        return success($data);
    }

    public function save(){
        $file = $this->request->file();
        if(!$this->user){
            return error(['desc'=> '请先登录,然后发表评论！'],'请先登录,然后发表评论！','401');
        }
        //没有订单的情况下，需要传入type和id
        if(!$this->has('order_id') || !$this->param('order_id','0')){
            if(!$this->check('type',['number','r','min:0'])||
                !$this->check('main_id',['number','r','min:0'])){
                return error($this->checkError());
            }
        }
        if(!$this->check('reply_id',['number','min:0'])){
            return error($this->checkError());
        }
        if(!$this->check('content',['string','r','full'])){
            return error(['desc'=>'请输入评论内容']);
        }
        if(!$this->check('star',['number','min:0','max:5'])){
            return error(['desc'=>'评分只能在0到5分之间']);
        }

        if($file){
            if(!is_array($file['img'])){
                $file['img'] = [$file['img']];
            }
            $img_result = model('image','service')->upload($file['img'],112);
            $this->param['img_id'] = array_column($img_result,'id');
        }
        if(isset($this->param['img_id'])){
            if(!is_array($this->param['img_id'])){
                $this->param['img_id'] = [$this->param['img_id']];
            }
        }else{
            $this->param['img_id'] = [];
        }

        $result = model('comment','service')->add($this->user, $this->param('type','103'), $this->param('main_id',''), $this->param('content',''), $this->param('reply_id',0), $this->param('star',5), $this->param['img_id'], $this->param('order_id',0),$this->param('msg_id',0));
        if($this->isError($result)){
            return error($result);
        }
        return success($result);
    }

    //
    public function delete(){
        if(!$this->user){
            return error(['desc'=> '请先登录,然后删除评论！'],'请先登录,然后删除评论！','401');
        }
        $res = model('comment','service')->delete($this->user,$this->param('id',0));
        if($this->isError($res)){
            return error($res);
        }
        return success($res);
    }
}