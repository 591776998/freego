<?php
namespace app\common\model;
use app\common\model;

class PlaceN extends model\BaseNodelete{

    protected $table = 'place';

    //在获取内容时，如果是
    public function img()
    {
        return $this->hasOne('\app\common\model\Image','id','img_id')->bind([
            'img_url'   => 'url'
        ]);
    }

    public function album(){
        return $this->belongsToMany('\app\common\model\Image', 'place_album', 'img_id', 'place_id')->where('pivot.del_time',0);
    }

    public function services(){
        return $this->hasMany('Services','place_id','id')->with(['img']);
    }

    public function collect(){
        return $this->hasOne('Collect','target_id','id')->where('type','104');
    }

    public function seller(){
        return $this->hasOne('Seller','id','seller_id');
    }

    public function park(){
        return $this->hasMany('PlacePark','place_id','id');
    }

    protected function getRunTimeAttr($value,$data)
    {

        return formatTimeStr($data['run_s_time'],3).'-'.formatTimeStr($data['run_e_time'],3);
    }

    public function tag(){
        return $this->belongsToMany('ArtTag', 'place_tag_relation', 'tag_id', 'place_id');
    }

    public function comments(){
        return $this->hasMany('Comment','main_id','id')->with(['user'=>function($query){
            return $query->with('head_img');
        },'reply_user'=>function($query){
            return $query->with('head_img');
        },'imgs'])->where('type',106);
    }
}