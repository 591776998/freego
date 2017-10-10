<?php
namespace app\index\service;
class Follow extends \app\common\service\Base
{

    public function add($user_id, $id){
        if(!$user_id || !$id || !is_numeric($user_id) || !is_numeric($id)){
            return $this->error(['desc'=>'关注失败'],'关注失败','702');
        }
        if($user_id == $id){
            return $this->error(['desc'=>'不能关注自己']);
        }
        $target_model = model('UserFollow');
        if($this->isFollow($user_id,$id)){
            return $this->error(['desc'=>'您已经关注了该用户'],'您已经关注了该用户','701');
        }else{
            $b_follow_a = $this->isFollow($id,$user_id);
            if($b_follow_a){
                $res = model('Netease','service')->request('add_friend',['accid'=>$user_id,'faccid'=>$id,'type'=>'1','msg'=>'对方关注了您！']);
                if($this->isError($res)){
                    return $this->error(['desc'=>'操作失败！']);
                }
            }
            if($target_model->data([
                'user_id'   => $user_id,
                'b_user_id' => $id,
                'c_time'    => THINK_START_TIME,
                'del_time'  => '0'
            ])->save()){
                return $this->success($target_model);
            }else{
                return $this->error(['desc'=>'关注失败'],'关注失败','702');
            }
        }
    }

    public function delete($user_id, $id){
        if(!$user_id || !$id || !is_numeric($user_id) || !is_numeric($id)){
            return $this->error(['desc'=>'取消关注失败'],'取消关注失败','702');
        }

        $is_friend = $this->isFriend($user_id,$id);
        if($is_friend){
            //先删除云信的好友关系
            $res = model('Netease','service')->request('del_friend',['accid'=>$user_id,'faccid'=>$id]);
            if($this->isError($res)){
                if(414 != $res['code'] || 'not friend' !=  $res['desc']){
                    return $this->error(['desc'=>'取消关注失败！']);
                }
            }
        }
        $record = model('UserFollow')->where('user_id',$user_id)->where('b_user_id',$id)->find();
        if($record && ($record['user_id'] == $user_id)){
            if($record->destroy($record->id)){
                return $this->success($record);
            }else{
                return $this->error(['desc'=>'取消关注失败了！']);
            }
        }else{
            return $this->error(['desc'=>'您没有关注该用户！'.$user_id.'._'.$record['user_id'].'._'.$id]);
        }
    }

    //互相关注
    public function addEachOther($a_id,$b_id){
        if($a_id == $b_id){
            return $this->error(['desc'=>'不能添加自己为好友']);
        }
        $a_is_follow = $this->isFollow($a_id,$b_id);
        if(!$a_is_follow){
            if(!model('UserFollow')->data([
                'user_id'   => $a_id,
                'b_user_id' => $b_id,
                'c_time'    => floor(THINK_START_TIME),
                'del_time'  => '0'
            ])->isUpdate(false)->save()){
                return $this->error(['添加好友失败!']);
            }
        }
        $b_is_follow = $this->isFollow($b_id,$a_id);
        if(!$b_is_follow){
            if(!model('UserFollow')->data([
                'user_id'   => $b_id,
                'b_user_id' => $a_id,
                'c_time'    => floor(THINK_START_TIME),
                'del_time'  => '0'
            ])->isUpdate(false)->save()){
                return $this->error(['添加好友失败!']);
            }
        }
        return $this->success([]);
    }

    //互相取消关注
    public function deleteEachOther($user_id,$id){
        if(!$user_id || !$id || !is_numeric($user_id) || !is_numeric($id)){
            return $this->error(['desc'=>'删除失败'],'删除失败','702');
        }
        $is_friend = $this->isFriend($user_id,$id);
        if($is_friend){
            //先删除云信的好友关系
            $res = model('Netease','service')->request('del_friend',['accid'=>$user_id,'faccid'=>$id]);
            if($this->isError($res)){
                if(414 != $res['code'] || 'not friend' !=  $res['desc']){
                    return $this->error(['desc'=>'删除失败！']);
                }
            }
        }
        $record = model('UserFollow')
        ->where(function ($query)use($user_id,$id){
            $query->where('user_id',$user_id)->where('b_user_id',$id);
        })->whereOr(function ($query)use($user_id,$id){
                $query->where('user_id',$id)->where('b_user_id',$user_id);
        })->update(['del_time'=>floor(THINK_START_TIME)]);
        return $this->success((object)null);
    }

    //添加好友请求
    public function addFriendRequest($user_id, $id, $content = ''){
        if($user_id == $id){
            return $this->error(['desc'=>'不能添加自己为好友']);
        }
        //1.两人是否是好友
        if($this->isFriend($user_id,$id)){
            return $this->error(['desc'=>'你们已经是好友了！']);
        }
        //还不是好友
        $target_model = model('UserFollowRequest')->where('user_id',$user_id)->where('b_user_id',$id)->find();
        if($target_model){
            //更新消息的时间
            model('UserFollowRequest')->where('id',$target_model['id'])->update(
                ['c_time'=>floor(THINK_START_TIME)]
            );
        }else{
            $target_model = model('UserFollowRequest')->data([
                'user_id'   => $user_id,
                'b_user_id' => $id,
                'content'   => $content,
                'c_time'    => THINK_START_TIME,
                'del_time'  => '0'
            ]);
            if(!$target_model->save()){
                return $this->error(['desc'=>'发送好友请求失败！']);
            }
        }

        //给云信说要加好友
        $res = model('Netease','service')->request('add_friend',['accid'=>$user_id,'faccid'=>$id,'type'=>'2','msg'=>'请求成为好友！']);
        if($this->isError($res)){
            return $this->error(['desc'=>$res['desc'],'full'=>$res],'发送请求失败');
        }
        $this->add($user_id,$id);

        return $this->success($target_model);
    }

    //同意或拒绝好友请求
    public function updateRequest($user_id,$id,$action){
        if($this->isFriend($user_id,$id)){
            return $this->error(['desc'=>'你们已经是好友了！']);
        }

        //如果是同意,就互相关注
        if(101 == $action){
            $res = model('Netease','service')->request('add_friend',['accid'=>$id,'faccid'=>$user_id,'type'=>'3','msg'=>'对方同意了您的好友请求！']);
            if($this->isError($res)){
                return $this->error(['desc'=>'操作失败！','info'=>$res],'操作失败！');
            }
            $res2 = $this->addEachOther($user_id,$id);
            if($this->isError($res2)){
                return $this->error(['desc'=>'添加好友失败！']);
            }
        }else{
            $res = model('Netease','service')->request('add_friend',['accid'=>$id,'faccid'=>$user_id,'type'=>'4','msg'=>'对方拒绝了您的加好友请求！']);
            if($this->isError($res)){
                return $this->error(['desc'=>'操作失败！']);
            }
        }
        return $this->success($res);
    }

    //判断两个人是否是好友
    protected function isFriend($user_id,$id){
        $count = model('UserFollow')
            ->where(function ($query)use($user_id,$id){
                $query->where(function ($query2)use($user_id,$id){
                    $query2->where('user_id',$user_id)->where('b_user_id',$id);

                })->whereOr(function ($query)use($user_id,$id){
                    $query->where('b_user_id',$user_id)->where('user_id',$id);
                });
            })
            ->count();
        if(1 < $count){
            return true;
        }else{
            return false;
        }
    }

    //判断A是否关注了B
    protected function isFollow($a,$b){
        if(model('UserFollow')->where('user_id',$a)->where('b_user_id',$b)->find()){
            return true;
        }else{
            return false;
        }
    }

    //发送推送
    protected function sendPush(){

    }
}