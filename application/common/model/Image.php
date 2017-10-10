<?php
namespace app\common\model;

class Image extends \app\common\model\Base{

    public function getUrlAttr($value,$data){
        return img_show($value);
    }

}