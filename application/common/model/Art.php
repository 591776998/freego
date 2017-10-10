<?php
namespace app\common\model;
use app\common\model;

class Art extends model\Base{

    public function forum(){
        return $this->hasOne('Forum','id','forum_id')->bind(['forum_title'=>'title']);
    }

    //论坛是多对多
    public function forums(){
        return $this->belongsToMany('Forum', 'art_forum_relation', 'forum_id', 'art_id');
    }

    public function user(){
        return $this->hasOne('User','id','user_id')->with(['headImg']);
    }

    public function comments(){
        return $this->hasMany('Comment','main_id','id')->with(['user'=>function($query){
            return $query->with('head_img');
        },'reply_user'=>function($query){
            return $query->with('head_img');
        },'imgs'])->where('type',103);
    }

    public function likes(){
        return $this->hasMany('Like','main_id','id')->with(['user'])->where('type','103');
    }

    public function like(){
        return $this->hasOne('Like','main_id','id')->where('type','103');
    }

    public function answer(){
        return $this->hasMany('ArtAnswer','main_id','id');
    }

    public function tag(){
        return $this->belongsToMany('ArtTag', 'art_tag_relation', 'tag_id', 'art_id')->where('pivot.del_time','0');
    }

    public function content(){
        //1.根据id查找对应的内容片断
        return $this->hasMany('ArtPara','main_id','id')->with(['img','video'])->order('order_weight','asc');
    }

    public function img()
    {
        return $this->hasOne('\app\common\model\Image','id','img_id')->bind([
            'img_url'   => 'url'
        ]);
    }

    public function line()
    {
        return $this->hasOne('Line','id','line_id')->with(['point']);
    }

    //
    public function linemap(){
        return $this->hasOne('Line','id','line_id')->with(['item']);
    }

    public function plan(){
        return $this->hasMany('Plan','art_id','id');
    }

    public function collect(){
        return $this->hasOne('Collect','target_id','id')->where('type','103');
    }

    // 定义关联方法
    public function companion()
    {
        return $this->belongsToMany('User', 'art_companion', 'user_id', 'main_id')->where('pivot.del_time','0')->order('`pivot`.id asc')->with(['headImg']);
    }

    //
    public function place(){
        return $this->hasOne('Place','id','place_id')->bind([
            'place_title'   => 'title'
        ]);
    }

    public function car(){
        return $this->hasOne('CarModel','id','car_model_id')->bind([
            'pre_car_name'   => 'title'
        ]);
    }

    public function act(){
        return $this->hasOne('artActivity','id','id');
    }

    //获取是否是置顶
    public function isTop(){
        return $this->hasOne('forumTop','art_id','id')->where('status',101)->where(function ($query){
            $query->where('exp_time',0)->whereOr('exp_time','>',floor(THINK_START_TIME));
        });
    }

    public function isBest(){
        return $this->hasOne('ForumBest','art_id','id');
    }
}