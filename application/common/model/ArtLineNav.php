<?php
namespace app\common\model;
use app\common\model;

class ArtLineNav extends model\Base{

    public function getDataAttr($value){
        return json_decode($value,true);
    }

    public function setDataAttr($value){
        return json_encode($value);
    }
}