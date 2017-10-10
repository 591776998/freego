<?php
namespace app\index\service;
class Chanel extends \app\common\service\Base
{

    //栏目列表
    public function index( $type, $p = false, $ps = false){
        $chanel = model('chanel')->with(['jump','img']);
        if($type)$chanel->where('type',$type);
        if($p && $ps)$chanel->page($p,$ps);
        $data = $chanel->order('order_weight desc')->select();
        return $this->success($data);
    }

}