<?php
namespace app\index\service;
class Msg extends \app\common\service\Base
{
    //定义类型
    protected $session_type      = 101; //101通知 102请求
    protected $session_sub_type  = 101; //101->100普通消息 101关注 102赞 103活动报名 104评论 105回复 106订单消息  102-> 101好友请求 102参加活动请求 103蹭车请求

    //发送方
    protected $sender_id         = 0;//消息发送方id
    protected $sender_type       = 0;//消息发送方类型 100系统 101普通用户 102地点 103商家用户

    //接收方
    protected $target_type       = 99;// 99推送会话 100全体用户 101某用户 102某标签 110地点 111商家
    protected $target            = [];//目标数组

    //内容
    protected $title             = '';//标题
    protected $content           = '';//内容
    protected $addi              = '';//附加消息内容
    protected $img_id            = '0';//图片


    //操作
    protected $action_status     = 99;//状态 101待处理 102已经同意 103未同意
    protected $action_id         = 0;
    protected $action_type       = 101;//操作的类型

    //推送相关
    protected $push              = null;
    protected $push_id           = '0';

    //错误
    protected $error_desc        = '';
    protected $error_code        = 200;

    protected $session_id        = [];

    public function _init()
    {
        $this->session           = [];//如果会话是单条会话，目标就可以分开，否则目标就
        $this->push              = null;
        $this->push_id           = '0';
        $this->title             = '';//标题
        $this->content           = '';//内容
        $this->error_desc        = '';
        $this->error_code        = 200;
        $this->arget_type        = 99;// 99推送会话 100全体用户 101某用户 102某标签 110地点 111商家
        $this->target            = [];//目标数组
    }

    //添加会话类型
    public function type($session_type = false, $session_sub_type = false){
        if(false !== $session_type)$this->session_type = $session_type;
        if(false !== $session_sub_type)$this->session_sub_type = $session_sub_type;
        return $this;
    }

    //添加发送方
    public function sender($sender_id = false,$sender_type = false){
        if(false !== $sender_id)$this->sender_id = $sender_id;
        if(false !== $sender_type)$this->sender_type = $sender_type;
        return $this;
    }

    //添加接收方 target 可以是数组 target_type使用最后一次赋值的值
    public function target( $target = false, $target_type = false){
        if(false !== $target){
            if(is_array($target)){
                $this->target             = array_merge($this->target,$target);
            }else{
                $this->target[]           = $target;
            }
        }
        if(false !== $target_type)$this->target_type = $target_type;
        return $this;
    }

    //添加对应操作
    public function action($action_status = false, $action_id = false, $action_type = false){
        if(false !== $action_status)$this->action_status = $action_status;
        if(false !== $action_id)$this->action_id = $action_id;
        if(false !== $action_type)$this->action_type = $action_type;
        return $this;
    }

    //添加消息内容
    public function content($content = false, $title = false){
        if(false !== $content)$this->content .= $content;
        if(false !== $title)$this->title .= $title;
        return $this;
    }

    //添加附加消息内容
    public function addi($data = false){
        if(false !== $data)$this->addi .= $data;
        return $this;
    }

    //添加消息图片
    public function img($msg_img = false){
        if(false !== $msg_img)$this->img_id = $msg_img;
        return $this;
    }

    //发送
    public function send(){
        $save_data = [
            'sender_id'         => $this->sender_id,
            'sender_type'       => $this->sender_type,
            'title'             => $this->title,
            'content'           => $this->content,
            'addi'              => $this->addi,
            'session_type'      => $this->session_type,
            'session_sub_type'  => $this->session_sub_type,
            'target_type'       => $this->target_type,
            'status'            => 101,
            'm_time'            => floor(THINK_START_TIME),
            'del_time'          => 0,
            'action_status'     => $this->action_status,
            'action_type'       => $this->action_type,
            'action_id'         => $this->action_id,
            'img_id'            => $this->img_id
         ];
        $model = model('MsgSession');
        if(is_array($this->target) && (1 < count($this->target))){
            $save_data_arr = array_map(function($t)use($save_data){
                                            $save_data['target_id'] = $t;
                                            return $save_data;
                                        },$this->target);
            $res = $model->saveAll($save_data_arr);
        }else{
            $save_data['target_id'] = current($this->target);
            $res = $model->save($save_data);
            $this->session_id = $model->id;
        }
        if($res){
            return $this;
        }else{
            //赋值错误信息
            $this->setError('发送消息失败！');
            return $this;
        }
    }

    //设置错误信息
    protected function setError($desc = '', $code = 201){
        $this->error_code = $code;
        $this->error_desc = $desc;
    }

    //获取错误信息
    public function getError(){
        if(200 != $this->error_code){
            return $this->error(['desc'=>$this->error_desc],$this->error_desc,$this->error_code);
        }else{
            return $this->success(['target'=>$this->target]);
        }
    }

    //推送
    public function push($content = '',$title = ''){
        //这里面，如果发生了更换推送会话的操作，那么就把推送会话赋值给会话
        $data = [
            'title'     => ('' != $title)?$title:$this->title,
            'content'   => ('' != $content)?$content:$this->content,
            'type'      => $this->session_type,
        ];
        $push_res = model('Push','service')->data($data)->push( false, $this->target_type, $this->target);
        return $this;
    }

    //创建一个推送用的会话
    public function addPushSession($data = []){
        $model_session = model('session')->data([
            'title'         => 'push_session',
            'user_id'       => '0',
            'session_type'  => '99',
            'target_type'   => '0',
            'target_id'     => '0',
            'status'        => '99',
            'm_time'        => '0',
            'del_time'      => '1'
        ]);
        if($model_session->save()){
            if($data){
                $model = model('msgPushId')->data([
                    'session_id'    => $model_session->id,
                    'user_session'  => json_encode($data)
                ]);
                if($model->save()){
                    return $model_session->id;
                }
            }
            return false;
        }else{
            return false;
        }
    }

}
