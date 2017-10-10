<?php
namespace app\seller\controller;
class Test extends \app\common\controller\SellerBase
{

    public function index(){

        return $this->fetch();
    }

    public function read(){


        return '';
    }

    public function save(){
        $file = $this->request->file();
        debug($file);
        $img_result = model('image','service')->upload($file,115);
        debug($img_result[0]);
        debug($img_result);
//        return error(['desc'=>'1']);

    }
}