<?php
namespace app\main\controller;
class Group extends \app\common\controller\MainBase
{

    public function save(){
        //创建1个群组
        $img = '';
        if($this->param('icon','')){
            $img = $this->param('icon');
            if(is_numeric($img)){
                $img = model('image','service')->getUrl( $img);
                if($img){
                    $img = $img['url'];
                }
            }
        }
        if(!$img){
            $img = $this->user['head_img_url'];
        }
        $res = model('Netease','service')->request('create_group',[
            'tname'         => $this->param('title','群组'),
            'owner'         => $this->user['id'],
            'icon'          => $img,
            'members'       => json_encode([$this->user['id']]),
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
        return success($res['data']);
    }
}