<?php
namespace app\docking\controller;
class Correct extends \app\common\controller\DockingBase
{
    public function index(){

        return '该接口未开放';
        $user = model('user')->where('id',20)->select();

        foreach ($user as $k => $v){
            //1.
            $user_follow = new \app\index\model\UserFollow();
            $f = $user_follow->where(function ($query)use($v){
                $query->where('user_id',$v['id'])->whereOr('b_user_id',$v['id']);
            })->select();

            $follow_arr = [];
            $fans_arr   = [];
            $friend_arr = [];

            $save_data = [
                'id'            => $v['id'],
                'friend_count'  => 0,
                'follow_count'  => 0,
                'fans_count'    => 0
            ];
            foreach ($f as $key => $val){
                if($val['user_id'] == $val['b_user_id']){
                    if(!in_array($val['b_user_id'],$follow_arr)){
                        $follow_arr[]   = $val['b_user_id'];
                    }
                    if(!in_array($val['user_id'],$fans_arr)){
                        $fans_arr[]   = $val['user_id'];
                    }
                    if(!in_array($val['user_id'],$friend_arr)){
                        $friend_arr[] = $val['user_id'];
                    }

                }else if($val['user_id'] == $v['id']){
                    $follow_arr[] = $val['b_user_id'];
                    if(in_array($val['b_user_id'],$fans_arr) && !in_array($val['b_user_id'],$friend_arr)){
                        $friend_arr[] = $val['b_user_id'];
                    }
                }elseif ($val['b_user_id'] == $v['id']){
                    $fans_arr[]   = $val['user_id'];
                    if(in_array($val['user_id'],$follow_arr) && !in_array($val['user_id'],$friend_arr)){
                        $friend_arr[] = $val['user_id'];
                    }
                }
            }
            $save_data['friend_count']  = count($friend_arr);
            $save_data['follow_count']  = count($follow_arr);
            $save_data['fans_count']  = count($fans_arr);
//            model('user')->where('id',$save_data['id'])->update($save_data);
        }
    }

    //同步点赞数
    public function like(){
        return '该接口未开放';
        //1.查出全部点赞的记录
        $like_mdoel = new \app\index\model\Like();

        $l = $like_mdoel->select();

        //1.文章
        $art = new \app\index\model\Art();
        $a = $art->select();
        foreach ($a as $v){
            $v->like_count = $this->getLikeCount($l,103,$v['id']);
            $v->save();
        }

        //评论
        $comment = new \app\index\model\Comment();
        $c = $comment->select();
        foreach ($c as $v){
            $v->like_count = $this->getLikeCount($l,107,$v['id']);
            $v->save();
        }
        return '结束';
    }

    //传入type和id，获取对应点赞数
    protected function getLikeCount($like_arr,$type,$id){
        $count = 0;
        foreach ($like_arr as $v){
            if($v['type'] == $type && $v['main_id'] == $id){
                $count++;
            }
        }
        return $count;
    }

    //
    public function delFollowSelf(){
        return '该接口未开放';
        $user_follow = new \app\index\model\UserFollow();
        $user_follow->where('user_id','`b_user_id`')->update(['del_time'=>floor(THINK_START_TIME)]);

    }


}
