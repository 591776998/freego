<?php
namespace app\index\controller;
use think\Request;
class Group extends \app\common\controller\Base
{
    protected $default_img_id   = 0;
    protected $default_img_url  = '';

    //创建
    public function save(){
        $file = $this->request->file();

        //调用云信，创建群组
        if(!$this->user){
            return error(['desc'=> '请先登录,然后创建群组！'],'请先登录,然后创建群组！','401');
        }
        $user = model('User','service')->fullModel($this->user['id']);
        if(!$user){
            return error(['desc'=> '请先登录,然后创建群组！'],'请先登录,然后创建群组！','401');
        }
        if($this->has('title')){
            $title = $this->param('title');
        }else{
            $title = $user['nick_name'].'的群聊';
        }

        if($file){
            if($file['img']){
                if(is_array($file['img'])){
                    $img = current($file['img']);
                }else{
                    $img = $file['img'];
                }
                $img_result = model('image','service')->upload($img,111);
                $img_id = $img_result['id']?$img_result['id']:$this->default_img_id;
            }
        }
        if(!isset($img_id)){
            if($this->has('img_id')){
                $img_id = $this->param('img_id');
            }else{
                $img_id = $this->default_img_id;
            }
        }
        $img_url = $this->default_img_url;

        //.1,。建群
        $res = model('Netease','service')->request('create_group',[
                                                                    'tname'         => $title,
                                                                    'owner'         => $user['id'].'',
                                                                    'icon'          => $img_url,
                                                                    'members'       => json_encode([$user['id']]),
                                                                    'msg'           => '您好，欢迎加入群聊！',
                                                                    'magree'        => 0,
                                                                    'joinmode'      => 0,
                                                                    'beinvitemode'  => 1,
                                                                    'invitemode'    => 1,
                                                                    'uptinfomode'   => 1,
                                                                    'upcustommode'  => 0
                                                                ]);

        if($this->isError($res)){
            return error(['desc'=>'新建群组失败！','info'=>$res],'新建群组失败！');
        }

        //入库
        $save_data = [
            'title'     => $title,
            'user_id'   => $user['id'],
            'img_id'    => $img_id,
            'tid'       => $res['data']['tid'],
        ];
        $group_model = model('Group');
        if($group_model->save($save_data)){
            return success(model('Group')->with(['img'])->where('id',$group_model['id'])->find());
        }else{
            return error(['desc'=>'创建群聊失败！']);
        }
    }


}