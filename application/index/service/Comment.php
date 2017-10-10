<?php
namespace app\index\service;
use app\index\model as model;
class Comment extends \app\common\service\Base
{
    public function read($user,$id){
        $now_user_id = $user?$user['id']:'-1';
        $data = model('comment')->with([
            'user',
            'reply_user',
            'imgs',
            'parent',
            'like'=>function($query)use($now_user_id){
                return $query->where('user_id',$now_user_id?$now_user_id:'-1');
            }
        ])->where('id',$id)->find();
        if($data){
            $data->toArray();
            if($data['imgs']){
                $data['imgs'] = array_map(function($i){
                    if(isset($i['img'])){
                        return $i['img'];
                    }else{
                        return (object)null;
                    }
                },$data['imgs']);
            }
            return $this->success($data);
        }else{
            return $this->error(['desc'=>'该评论不存在！']);
        }
    }

    //评论列表
    public function index($user, $type, $main_id, $user_id, $page, $pagesize)
    {
        $now_user_id = $user?$user['id']:'-1';
        $model = new model\Comment();
        $model->with([
            'user',
            'reply_user',
            'imgs',
            'parent'=>function($query){
                return $query->with(['user']);
            },
            'like'=>function($query)use($now_user_id){
                return $query->where('user_id',$now_user_id?$now_user_id:'-1');
            }
        ]);
        if($type){
            $model->where('type',$type);
        }
        if($main_id){
            if(is_array($main_id)){
                $model->where('main_id','in',$main_id);
            }else{
                $model->where('main_id',$main_id);
            }
        }
        if($user_id){
            $model->where('user_id',$user_id);
        }
        if($page&&$pagesize){
            $model->page($page,$pagesize);
        }
        $data = $model->order('id desc')->select();
        if($data){
            $data = array_map(function($item){
                $temp = $item->toArray();
                $temp['imgs'] = array_map(function($i){
                    if(isset($i['img'])){
                        return $i['img'];
                    }else{
                        return (object)null;
                    }
                },$temp['imgs']);
                return $temp;
            },$data);
            return $this->success($data);
        }
        return $this->success([]);
    }

    public function add($user, $type, $main_id, $content = '', $reply_id = 0, $star = 5, $img_id = [], $order_id = 0,$msg_id = 0){
        //根据reply_id获取reply_user_id
        if($order_id){
            //查询出被评论的是商品还是用户还是商家
            $order_data = model('order')->where('id',$order_id)->find();
            if(!$order_data){
                return $this->error(['desc'=>'您评价的订单不存在或者已经失效！']);
            }else{
                //100扫码消费 101购买服务/商品 102参加活动 103蹭车
                switch ($order_data['order_type']){
                    case '100':
                        $type    = '106';
                        $main_id = $order_data['seller_id'];
                        break;
                    case '101':
                        $type = '104';
                        //找出全部子项
                        $order_sub = model('orderItem')->where('order_id',$order_data['id'])->select();
                        if($order_sub){
                            $main_id = array_map(function($sub){
                                return $sub['services_id'];
                            },$order_sub);
                        }
                        break;
                    case '102':
                        $type      = '103';
                        $main_id   = $order_data['activity_id'];
                        break;
                    case '103':
                        $type = '112';
                        $main_id = $order_data['seller_id'];
                        break;
                    default:
                        return $this->error(['desc'=>'暂时不支持评论该类型订单！']);
                }
                $reply_id = 0;
            }
        }

        if($reply_id){
            $temp_c = model('comment')->where('id',$reply_id)->find();
            $reply_user_id = isset($temp_c['user_id'])?$temp_c['user_id']:'0';
        }else{
            $reply_user_id = 0;
        }

        if(is_array($main_id)){
            $comment_save = [];
            foreach (array_unique($main_id) as $v) {
                $comment_save[] = [
                    'main_id'       => $v,
                    'type'          => $type,
                    'reply_id'      => $reply_id,
                    'reply_user_id' => $reply_user_id,
                    'user_id'       => $user['id'],
                    'content'       => $content,
                    'star'          => $star,
                ];
            }
            $res = $this->addComments($comment_save,$img_id);
        }else{
            $comment_save = [
                'main_id'       => $main_id,
                'type'          => $type,
                'reply_id'      => $reply_id,
                'reply_user_id' => $reply_user_id,
                'user_id'       => $user['id'],
                'content'       => $content,
                'star'          => $star,
            ];
            $res = $this->addComments($comment_save,$img_id);
        }

        if($res){
            if(is_array($main_id)){
                $comment = current($res);
                if($comment){
                    $comment_id = isset( $comment->id)?$comment->id:0;
                }else{
                    $comment_id = 0;
                }
            }else{
                $comment_id = isset($res->id)?$res->id:0;
            }

            if($msg_id){
                //设置消息为已经处理
                model('msg_session')->where('id',$msg_id)->update(['action_status'=>'104']);
            }

            //添加消息，通知有人评论了，消息的详细类型 101-> 101关注 102赞 103活动报名 104评论 105回复 106订单消息 102->101好友请求 102蹭车请求 103参加活动请求
            // type 103文章 104服务 106地点 111乘客的评论 112车主的评论 TODO 服务和地点暂时不要消息
            switch ($type){
                case '103':
                    if(is_array($main_id)){
                        $main_id = current($main_id);
                    }
                    $main_target = model('art')->where('id',$main_id)->find();
                    if($main_target){
                        if($img_id && is_array($img_id)){
                            $msg_img = current($img_id);
                        }else{
                            $msg_img = 0;
                        }
                        if(!$msg_img){
                            $msg_img = $main_target['img_id'];
                        }
                        $target_user_id  = $main_target['user_id'];
                        if($reply_id){
                            $pre = '在评论中回复了您：';
                        }else{
                            $pre = '评论了您的'.model('art','service')->showArtType($main_target['type']).'：';
                        }
                        $msg_title = $pre.$content;
                    }
                    break;
                case '111':
                case '112':
                    $main_target = ['id'=>$main_id,'type'=>$type];//model('user')->where('id',$main_id)->find();
                    if($main_target){
                        if($img_id && is_array($img_id)){
                            $msg_img = current($img_id);
                        }else{
                            $msg_img = 0;
                        }

                        $target_user_id  = $main_target['id'];
                        if(111 == $type){
                            $pre = '有车主';
                        }else{
                            $pre = '有乘客';
                        }
                        $msg_title = $pre.'对您作出了评价：'.$content;
                    }
                    break;
            }

            $add_point = 0;
            if(isset($main_target) && $main_target){
                if($user['id'] != $target_user_id){
                    model('msg','service')->type(101, 104)
                        ->sender( $user['id'],101)
                        ->target( $target_user_id, 101)
                        ->action(false, $main_id,$main_target['type'])
                        ->content('',$msg_title)
                        ->img($msg_img)
                        ->send()
                        ->push($content,$msg_title);
                    $add_point = model('system','service')->get('point_comment',1);
                }

                //如果是回复， 且 被回复的不是自己
                if($reply_id && ($reply_user_id != $user['id']) && ($reply_user_id != $target_user_id)){
                    model('msg','service')->type(101, 105)
                        ->sender( $user['id'],101)
                        ->target( $reply_user_id, 101)
                        ->action(false, $main_id,$main_target['type'])
                        ->content('',$msg_title)
                        ->img($msg_img)
                        ->send()
                        ->push($content,$msg_title);

                    $add_point = model('system','service')->get('point_comment',1);
                }
            }

            if($order_id){
                if(isset($order_data)){
                    //给用户加积分 ，设置订单为已评价
                    $order_data->comment_time = floor(THINK_START_TIME);
                    if($order_data->save()){
                        $add_point += ($order_data['total_back_points']);

//                    //给用户加减积分 todo
//                    $point_num = $order_data['total_back_points'] - $order_data['total_cons_points'];
//                    if(0 < $point_num){
//                        //加积分
//                        model('point','service')->add($point_num,$order_data['user_id'],101);
//                    }elseif (0 > $point_num){
//                        //减积分
//                        model('point','service')->del(abs($point_num),$order_data['user_id'],101);
//                    }

                    }
                }
            }

            if(0 < $add_point){
                model('point', 'service')->add($add_point, $user['id']);
            }elseif (0 > $add_point){
                model('point', 'service')->del(abs($add_point), $user['id']);
            }

            //如果是评论的地点，自动生成一条动态
            if(106 == $type && !$reply_id){
                $art_data = [
                    'title'     => '',
                    'art_type'  => 104,
                    'show_time' => floor(THINK_START_TIME),
                    'img_id'    => $img_id?(is_array($img_id)?current($img_id):$img_id):'0',
                    'place_id'  => $main_id,
                    'from'      => '107'
                ];

                $place_data = model('place')->where('id',$main_id)->find();
                if($place_data){
                    $art_data['title'] = $place_data['title'];
                }
                $art_data['content'] = [];
                $art_data['content'][] = [
                    'para_type'     => '101',
                    'content'       => $content,
                    'img_id'        => '0',
                    'video_id'      => '0',
                ];
                //循环图片生成图片段落
                if($img_id){
                    if(is_array($img_id)){
                        foreach ($img_id as $k => $v){
                            $art_data['content'][] = [
                                'para_type'     => '102',
                                'content'       => '',
                                'img_id'        => $v,
                                'video_id'      => '0',
                            ];
                        }
                    }else{
                        $art_data['content'][] = [
                            'para_type'     => '102',
                            'content'       => '',
                            'img_id'        => $img_id,
                            'video_id'      => '0',
                        ];
                    }
                }
                $result = model('Art','service')->addMain($user['id'], $art_data);
            }

            return $this->success(['id'=>$comment_id]);
        }else{
            return $this->error(['desc'=>'评论失败！请稍后再试！']);
        }
    }

    //批量新建评论
    protected function addComments($data = [], $img_id = [])
    {
        if ($data) {
            if (count($data) == count($data, 1)) {
                //1维
                $res = model('comment');
                $res->data([
                    'main_id'       => $data['main_id'],
                    'type'          => $data['type'], //$type,
                    'reply_id'      => $data['reply_id'], //$reply_id,
                    'reply_user_id' => $data['reply_user_id'], //$reply_user_id,
                    'user_id'       => $data['user_id'], //$user['id'],
                    'content'       => $data['content'], //$content,
                    'star'          => $data['star'], //$star,
                    'like_count'    => '0',
                    'status'        => '101',
                    'del_time'      => '0'
                ])->save();
                if ($res && $img_id) {
                    $this->addImg($res->id,$img_id);
                }
                return $res;
            } else {

                //2维
                $res = model('comment');
                $save_data = array_map(function($d){
                    return [
                        'main_id'       => $d['main_id'],
                        'type'          => $d['type'], //$type,
                        'reply_id'      => $d['reply_id'], //$reply_id,
                        'reply_user_id' => $d['reply_user_id'], //$reply_user_id,
                        'user_id'       => $d['user_id'], //$user['id'],
                        'content'       => $d['content'], //$content,
                        'star'          => $d['star'], //$star,
                        'like_count'    => '0',
                        'status'        => '101',
                        'del_time'      => '0'
                    ];
                },$data);
                $res_id = [];
                foreach ($save_data as $k => $v){
                    $res = model('comment');
                    if($res->data($v)->isUpdate(false)->save()){
                        $res_id[] = $res->id;
                    }
                }
                $this->addImg($res_id,$img_id);

                return $res;
            }
        }else{
            return [];
        }
    }

    //添加评论图片
    public function addImg($main_id,$img_id){
        $img_save = [];
        if($main_id && $img_id){
            foreach ($img_id as $v) {
                if ($v) {
                    if(is_array($main_id)){
                        foreach ($main_id as $m) {
                            $img_save[] = [
                                'comment_id' => $m,
                                'img_id'     => $v,
                                'del_time'   => '0'
                            ];
                        }
                    }else{
                        $img_save[] = [
                            'comment_id' => $main_id,
                            'img_id'     => $v,
                            'del_time'   => '0'
                        ];
                    }
                }
            }
        }
        if ($img_save)model('commentImg')->saveAll($img_save);
        return true;
    }

    //删除评论
    public function delete($user,$id){
        $record = model('comment')->where('id',$id)->find();
        if(!$record){
            return $this->error(['desc'=>'该评论不存在或已被删除']);
        }
        if($record->user_id != $user['id']){
            return $this->error(['desc'=>'您无法删除该评论']);
        }
        if($record->destroy($record->id)){
            return $this->success([]);
        }else{
            return $this->error(['desc'=>'删除失败']);
        }
    }
}