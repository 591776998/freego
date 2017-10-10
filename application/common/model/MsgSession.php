<?php
namespace app\common\model;
use app\common\model;

class MsgSession extends model\Base{

    //发布者
    public function user(){
        return $this->hasOne('User','id','sender_id')->with(['headImg'])->withCount(['commentPassenger']);
    }

    //地点卖家
    public function shop(){
        return $this->hasOne('Place','id','sender_id')->with(['img']);
    }

    //商户
    public function seller(){
        return $this->hasOne('Seller','id','sender_id')->with(['img']);
    }

    public function img()
    {
        return $this->hasOne('\app\common\model\Image','id','img_id')->bind(['img_url'   => 'url']);
    }

    //关注
    public function follow(){
        return $this->hasOne('UserFollow','b_user_id','sender_id');
    }

    //
    public function getTimeShowStrAttr($value,$data){
        return humanizedTime($data['m_time']);//img_show($value);
    }

}