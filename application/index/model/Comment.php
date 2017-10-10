<?php
namespace app\index\model;
use app\common\model;

class Comment extends model\Base{
    protected $pk = 'id';

    // 定义关联方法
    public function art()
    {
        return $this->belongsTo('Art','main_id','id',[],'LEFT');
    }

    public function imgs(){
        return $this->hasMany('CommentImg','comment_id','id')->with(['img']);
    }

    public function user(){
        return $this->hasOne('User','id','user_id')->with(['headImg']);
    }

    public function replyUser(){
        return $this->hasOne('User','id','reply_user_id')->with(['headImg']);
    }

    public function parent(){
        return $this->hasOne('Comment','id','reply_id');
    }

    public function like(){
        return $this->hasOne('Like','main_id','id')->where('type','107');
    }

}