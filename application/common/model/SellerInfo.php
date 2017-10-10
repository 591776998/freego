<?php
namespace app\common\model;
use app\common\model;

class SellerInfo extends model\Base{

    public function cardHoldImg(){
        return $this->hasOne('\app\common\model\Image','id','card_hold_img_id')->bind([
            'card_hold_img_url'   => 'url'
        ]);
    }

    public function cardFrontImg(){
        return $this->hasOne('\app\common\model\Image','id','card_front_img_id')->bind([
            'card_front_img_url'   => 'url'
        ]);
    }

    public function cardBackImg(){
        return $this->hasOne('\app\common\model\Image','id','card_back_img_id')->bind([
            'card_back_img_url'   => 'url'
        ]);
    }

    public function blImg(){
        return $this->hasOne('\app\common\model\Image','id','bl_img_id')->bind([
            'bl_img_url'   => 'url'
        ]);
    }
}