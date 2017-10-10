<?php
namespace app\index\service;
class Like extends \app\common\service\Base
{

    //点赞
    public function add( $user, $type, $id){
        // todo 这里把被删除的记录一起找出来 如果统计过积分了，那么新增的就直接设置成统计过
        $record = model('like')->where('main_id',$id)->where('user_id',$user['id'])->where('type',$type)->find();
        if($record){
            return $this->error(['desc'=>'您已经点过赞了！']);
        }else{
            $model = model('like')->data([
                'type'          => $type,
                'main_id'       => $id,
                'user_id'       => $user['id'],
                'status'        => '101'
            ]);
            if($model->isUpdate(false)->save()){
                //
                $total = model('like')->where('main_id',$id)->where('user_id',$user['id'])->where('type',$type)->count();

                $action_type = 0;
                switch ($type.''){
                    case '103':
                        $main_target = model('art')->where('id',$id)->find();
                        if($main_target) {
                            $msg_img        = $main_target['img_id'];
                            $target_user_id = $main_target['user_id'];
                            $msg_title      = '赞了您的' . model('art', 'service')->showArtType($main_target['type']);
                            $action_type    = $main_target['type'];
                        }
                        break;
                    case '107':
                        $main_target = model('comment')->with(['art'])->where('id',$id)->find();
                        if($main_target){
                            if($main_target['art'] && isset($main_target['art']['img_id'])){
                                $msg_img = $main_target['art']['img_id']?:'0';
                            }else{
                                $msg_img = '0';
                            }
                            $target_user_id = $main_target['user_id'];
                            $msg_title      = '赞了您的评论';
                            $action_type    = $main_target['type'];
                        }
                        break;
                    case '120':
                        $main_target = model('wonderfulImg')->where('id',$id)->find();
                        if($main_target){
                            $msg_img        = $main_target['img_id']?$main_target['img_id']:'0';
                            $target_user_id = $main_target['user_id'];
                            $msg_title      = '在精彩影像里赞了您';
                            $action_type    = 120;
                        }
                        break;
                }

                if(0 == $total%10){
                    $add_point1 = model('system','service')->get('point_like_other',1);
                    model('point','service')->add($add_point1, $user['id']);
                }

                //如果赞的是游记 动态 问答 评论 1个赞给自己加1定分数
                if(103 == $type){
                    $add_point = model('system','service')->get('point_like',1);
                    //文章
                    if(isset($main_target) && $main_target && in_array($main_target['type'],[101,103,104]) && ($main_target['user_id'] != $user['id'])){
                        model('point','service')->add($add_point, $user['id']);
                    }
                }elseif (107 == $type){
                    $add_point = model('system','service')->get('point_like',1);
                    //评论
                    if(isset($main_target) && $main_target && ($main_target['user_id'] != $user['id'])){
                        model('point','service')->add($add_point, $user['id']);
                    }
                }

                //添加消息，通知有人赞了，消息的详细类型 101-> 101关注 102赞 103活动报名 104评论 105回复 106订单消息 102->101好友请求 102蹭车请求 103参加活动请求
                if(isset($main_target)){
                    model('msg','service')
                        ->type(101, 102)
                        ->sender( $user['id'],101)
                        ->target( $target_user_id, 101)
                        ->action(101, $id,$action_type)
                        ->content('',$msg_title)
                        ->img($msg_img)
                        ->send()
                        ->push($user['nick_name'].''.$msg_title,'点赞');
                }

                return $this->success($model);
            }else{
                return $this->error(['desc'=>'点赞失败']);
            }
        }
    }

    //取消赞
    public function delete($user, $id){
        $record = model('like')->where('id',$id)->find();
        if($record && ($record['user_id'] == $user['id'])){
            if($record->destroy($record->id)){

                $total = model('like')->where('main_id',$record['main_id'])->where('user_id',$user['id'])->where('type',$record['type'])->count();
                if(0 == $total%9){
                    $add_point1 = model('system','service')->get('point_like_other',1);
                    model('point','service')->del($add_point1, $user['id'],101);
                }

                return $this->success($record);
            }else{
                return $this->error(['desc'=>'取消赞失败！']);
            }
        }else{
            return $this->error(['desc'=>'您没有赞该内容！']);
        }
    }
}