<?php
namespace app\common\service;
class Point extends \app\common\service\Base
{

    //加积分
    public function add($num, $id, $type = 101){
        //101user用户 102place地点 103seller商家用户
        if($num < 0){
            return $this->error(['desc'=>'错误的积分数！']);
        }
        switch ($type){
            case '101':
                //用户
                if(model('user')->where('id',$id)->setInc('point',$num)){
                    return $this->success([]);
                }else{
                    return $this->error(['desc'=>'积分转账失败！']);
                }
                break;
            case '102':
                //地点
                $place = model('place')->with(['seller'])->where('id',$id)->find();
                if(!$place){
                    return $this->error(['desc'=>'错误的商家！']);
                }
                if(!$place['seller_id'] || !$place['seller'] || !isset($place['seller']['id'])){
                    return $this->error(['desc'=>'该地点尚未被商家绑定，无法完成积分转账！']);
                }
                if(model('seller')->where('id',$place['seller_id'])->setInc('point',$num)){
                    return $this->success([]);
                }else{
                    return $this->error(['desc'=>'积分转账失败！']);
                }
                break;
            case '103':
                //商家
                if(model('seller')->where('id',$id)->setInc('point',$num)){
                    return $this->success([]);
                }else{
                    return $this->error(['desc'=>'积分转账失败！']);
                }
                break;
            default:
                return $this->error(['desc'=>'错误的类型！']);
        }
    }

    //减积分
    public function del($num, $id, $type=101){
        if($num < 0){
            return $this->error(['desc'=>'错误的积分金额！']);
        }
        //type 101普通用户 102地点 103商家
        switch ($type){
            case '101':
                if(model('user')->where('id',$id)->where('point','>',$num)->setDec('point',$num)){
                    return $this->success([]);
                }else{
                    return $this->error(['desc'=>'积分转账失败！']);
                }
                break;
            case '102':
                $place = model('place')->with(['seller'])->where('id',$id)->find();
                if(!$place){
                    return $this->error(['desc'=>'错误的商家！']);
                }
                if(!$place['seller_id'] || !$place['seller'] || !isset($place['seller']['id'])){
                    return $this->error(['desc'=>'该地点尚未被商家绑定，无法完成积分转账！']);
                }
                if(model('seller')->where('id',$place['seller_id'])->where('point','>',$num)->setDec('point',$num)){
                    return $this->success([]);
                }else{
                    return $this->error(['desc'=>'积分转账失败！']);
                }
                break;
            case '103':
                if(model('seller')->where('id',$id)->where('point','>',$num)->setDec('point',$num)){
                    return $this->success([]);
                }else{
                    return $this->error(['desc'=>'积分转账失败！']);
                }
                break;
            default:
                return $this->error(['desc'=>'错误的类型！']);
        }
    }

}