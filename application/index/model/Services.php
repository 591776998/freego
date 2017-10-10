<?php
namespace app\index\model;
use app\common\model;

class Services extends model\Base{

    //在获取内容时，如果是
    public function img()
    {
        return $this->hasOne('\app\common\model\Image','id','img_id')->bind([
            'img_url'   => 'url'
        ]);
    }

    public function plan()
    {
        return $this->hasMany('Plan','services_id','id');
    }

    //收藏
    public function collect(){
        return $this->hasOne('Collect','target_id','id')->where('type','106');
    }

    public function content(){
        //1.根据id查找对应的内容片断
        return $this->hasMany('ServicesPara','main_id','id')->with(['img','video'])->order('order_weight asc');
    }
}