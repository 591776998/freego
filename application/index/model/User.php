<?php
namespace app\index\model;
use app\common\model;

class User extends model\Base{

    public function userLogin()
    {
        return $this->hasOne('UserLogin','id','id');
    }

    public function headImg(){
        return $this->hasOne('\app\common\model\Image','id','head_img_id')->bind(['head_img_url'=>'url']);
    }

    public function coverImg(){
        return $this->hasOne('\app\common\model\Image','id','cover_img_id')->bind(['cover_img_url'=>'url']);
    }

    //是否被关注
    public function follow(){
        return $this->hasOne('UserFollow','b_user_id','id');
    }

    //是否关注
    public function isfollow(){
        return $this->hasOne('UserFollow','user_id','id');
    }

    //用户去过的地点统计
    public function footprintNum(){
        return $this->hasOne('UserFootprint','user_id','id')
            ->field('count(distinct s_code) as s_num,count(distinct c_code,city) as c_num,count(distinct place_id) as place_num,user_id')->group('user_id');
    }

    //用户去过的国家
    public function footprintCountry(){
        return $this->belongsToMany('AddressCountry', 'user_footprint', 'user_id', 'main_id')->order('`pivot`.id asc')->with(['headImg']);
    }

    //用户的车
    public function car(){
        return $this->hasMany('CarAuth','user_id','id')->with('carmodel')->order('order_weight asc');
    }

    //俱乐部
    public function club(){
        return $this->hasOne('Club','id','club_id')->bind(['club_title'=>'title']);
    }

    //云信信息
    public function uimUser(){
        return $this->hasOne('UserUim','user_id','id');
    }

    //荣誉
    public function honour(){
        return $this->hasOne('honor','level','level')->bind(['honor'=>'title']);
    }

    //用户作为车主的时候，被评价的列表
    public function commentMain(){
        return $this->hasMany('Comment','main_id','id')->where('type',112)->order('id desc');
    }

    //用户作为乘客时，被评价的列表
    public function commentPassenger(){
        return $this->hasMany('Comment','main_id','id')->where('type',111)->order('id desc');
    }

}