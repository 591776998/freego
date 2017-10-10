<?php
namespace app\seller\model;
use app\common\model;

class Place extends model\Base{

    public function tag(){
        return $this->belongsToMany('ArtTag', 'place_tag_relation', 'tag_id', 'place_id')->where('pivot.del_time',0);//->where();
    }

    public function album(){
        return $this->belongsToMany('\app\common\model\Image', 'place_album', 'img_id', 'place_id')->where('pivot.del_time',0);
    }

    public function park(){
        return $this->hasMany('PlacePark','place_id','id');
    }

    public function services(){
        return $this->hasMany('Services','place_id','id');
    }

    //在获取内容时，如果是
    public function img()
    {
        return $this->hasOne('\app\common\model\Image','id','img_id')->bind([
            'img_url'   => 'url'
        ]);
    }
}