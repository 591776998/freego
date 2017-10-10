<?php
namespace app\index\controller;
use think\Request;
class Msg extends \app\common\controller\Base
{

    public function index(){
        if(!$this->user){
//            return error(['desc'=>'请登录再查看消息！'],'请登录再查看消息！');
            return success([]);
        }
        $user_id = $this->user['id'];
        //查看我的消息列表
        $data = model('MsgSession')
            ->with(['user','seller','shop','img','follow'=>function($query)use($user_id){
                return $query->where('user_id',$user_id);
            }])
            ->where('session_type',$this->param('type',101))
            ->where(function ($query)use($user_id){
                $query->where('target_type',100)->whereOr(function ($query)use($user_id){
                    $query->where('target_id',$user_id)->where('target_type',101);
                });
            })
            ->page($this->param('page',1),$this->param('page_size',10))
            ->order('id desc')
            ->select();

        //更新对应的消息阅读表信息
        model('msgRead')->where('user_id',$user_id)->where('session_type',$this->param('type',101))->update(['last_time'=>floor(THINK_START_TIME)]);
        $data = array_map(function ($d){
            if(!isset($d['img_url']))$d['img_url'] = '';
            $d['sender'] = $this->getSender($d);
            $d['btn']    = $this->getBtn($d);
            $d['time_show_str'] = $d['time_show_str'];
            unset($d['user']);
            unset($d['seller']);
            unset($d['shop']);
            unset($d['follow']);
            return $d;
        },$data);
        return success($data);
    }

    //删除
    public function delete(){
        if(!$this->user){
            return error(['desc'=>'请登录再删除消息！']);
        }
        $msg = model('MsgSession')->where('id',$this->param('id',0))->find();
        if(!$msg){
            return error(['desc'=>'没找到该消息！']);
        }
        if($msg && $msg['target_id'] == $this->user['id']){
            if($msg->destroy($msg->id)){
                return success($msg);
            }else{
                return error(['desc'=>'删除失败！']);
            }
        }else{
            return error(['desc'=>'您不能删除该消息！']);
        }
    }

    /******************************************/
    //获取按钮模型
    protected function getBtn($data){
        //1.给type，
        //101-> 101关注 102赞 103活动报名 104评论 105回复 106订单消息  102-> 101好友请求 102活动请求 103蹭车请求
        $can_tap = true;
        $title      = '';
        $sub_title  = '';
        if(101 == $data['session_type']){
            switch ($data['session_sub_type']){
                case '101':
                    if($data['follow']){
                        $sub_title  = '已关注';
                        $can_tap = false;
                    }else{
                        $title      = '关注+';
                    }
                break;
                case '102':
                case '104':
                case '105':
                    if(101 == $data['action_type']){
                        $title = '游记 >';
                    }else if(102 == $data['action_type']){
                        $title = '活动 >';
                    }else if(103 == $data['action_type']){
                        $title = '问答 >';
                    }else if(104 == $data['action_type']){
                        $title = '动态 >';
                    }
                    break;
                case '106':
                    $title = '订单 >';
                    break;
            }
        }else if(102 == $data['session_type']){
            //101加好友请求 102蹭车请求 103参加活动请求
            switch ($data['session_sub_type']){
                case '101':
                    //#消息的状态 101待处理 102已经同意 103未同意
                    if(102 == $data['action_status']){
                        $sub_title = '已同意';
                        $can_tap   = false;
                    }else if (103 == $data['action_status']){
                        $sub_title = '已拒绝';
                        $can_tap   = false;
                    }else{
                        $title = '同意';
                    }
                    break;
                case '102':
                    //#消息的状态 101待处理 102已经同意 103未同意
                    if(102 == $data['action_status']){
                        $title     = '评价';
                        $sub_title = '已同意';
                    }else if (103 == $data['action_status']){
                        $sub_title = '已拒绝';
                        $can_tap   = false;
                    }else if(104 == $data['action_status']){
                        $title = '';
                        $sub_title = '已评价';
                        $can_tap   = false;
                    }else{
                        $title = '同意';
                    }
                    break;
                case '103':
                    //#消息的状态 101待处理 102已经同意 103未同意
                    if(102 == $data['action_status']){
                        $sub_title = '已同意';
                        $can_tap   = false;
                    }else if (103 == $data['action_status']){
                        $sub_title = '已拒绝';
                        $can_tap   = false;
                    }else{
                        $title = '同意';
                    }
                    break;
            }
        }
        return $this->iniBtn($data['action_id'] ,$title , $sub_title , $data['session_sub_type'] , $data['action_type'] , $data['action_status'] , isset($data['img_url'])?$data['img_url']:'' , $can_tap );
    }

    //初始化一个按钮模型
    protected function iniBtn($id = 0,$title = '', $sub_title = '', $type = 0,$sub_type = 0, $status = '', $img = '', $can_tap = true){
        return [
            'id'        => $id,
            'title'     => $title,
            'sub_title' => $sub_title,
            'type'      => $type,
            'sub_type'  => $sub_type,
            'status'    => $status,
            'img_url'   => $img,
            'can_tap'   => $can_tap?'101':'102'
        ];
    }

    //获取发送者统一模型
    protected function getSender($data){
        $sender = (object)null;
        switch ($data['sender_type']){
            case '100':
                //系统
                $sender = $this->iniSender(0,'系统消息','',$data['sender_type']);
            break;
            case '101':
                //普通用户
                if(isset($data['user']) && $data['user']){
                    $sender = $this->iniSender($data['user']['id'],$data['user']['nick_name'],isset($data['user']['head_img_url'])?$data['user']['head_img_url']:'',$data['sender_type'],isset($data['user']['comment_passenger_count'])?$data['user']['comment_passenger_count']:'0');
                }
                break;
            case '102':
                //地点
                if(isset($data['shop']) && $data['shop']){
                    $sender = $this->iniSender($data['shop']['id'],$data['shop']['title'],isset($data['shop']['img_url'])?$data['shop']['img_url']:'',$data['sender_type']);
                }
                break;
            case '103':
                //商家
                if(isset($data['seller']) && $data['seller']){
                    $sender = $this->iniSender($data['seller']['place_id'],$data['seller']['name'],isset($data['seller']['img_url'])?$data['seller']['img_url']:'',$data['sender_type']);
                }
                break;
        }
        if(!isset($sender) || !$sender){
            $sender = $this->iniSender();
        }
        return $sender;
    }

    //初始化一个发送者模型
    protected function iniSender($id = 0, $title = '', $img = '', $type = 0, $comment_count = 0){
        return [
            'id'        => $id,
            'title'     => $title,
            'img_url'   => $img,
            'type'      => $type,
            'comment_count' => $comment_count
        ];
    }
}