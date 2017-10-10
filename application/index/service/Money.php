<?php
namespace app\index\service;
use think\Db;
class Money extends \app\common\service\Base
{
    //内部零钱服务 简单点，简单点，简单。。。

    //加钱
    public function add($num, $id, $type){
        //101user用户 102place地点 103seller商家用户
        if($num < 0){
            return $this->error(['desc'=>'错误的金额！']);
        }

        switch ($type){
            case '101':
                if(model('user')->where('id',$id)->setInc('money',$num)){
                    return $this->success([]);
                }else{
                    return $this->error(['desc'=>'转账失败！']);
                }
                break;
            case '102':
                $place = model('place')->with(['seller'])->where('id',$id)->find();
                if(!$place){
                    return $this->error(['desc'=>'错误的商家！']);
                }
                if(!$place['seller_id'] || !$place['seller'] || !isset($place['seller']['id'])){
                    return $this->error(['desc'=>'该地点尚未被商家绑定，无法完成转账！']);
                }
                if(model('seller')->where('id',$place['seller_id'])->setInc('money',$num)){
                    return $this->success([]);
                }else{
                    return $this->error(['desc'=>'转账失败！']);
                }
                break;
            case '103':
                if(model('seller')->where('id',$id)->setInc('money',$num)){
                    return $this->success([]);
                }else{
                    return $this->error(['desc'=>'转账失败！']);
                }
                break;
            default:
                return $this->error(['desc'=>'错误的类型！']);
        }
    }

    //减钱
    public function del($num, $id, $type){
        if($num < 0){
            return $this->error(['desc'=>'错误的金额！']);
        }
        //type 101普通用户 102地点 103商家
        switch ($type){
            case '101':
                if(model('user')->where('id',$id)->setDec('money',$num)){
                    return $this->success([]);
                }else{
                    return $this->error(['desc'=>'转账失败！']);
                }
                break;
            case '102':
                $place = model('place')->with(['seller'])->where('id',$id)->find();
                if(!$place){
                    return $this->error(['desc'=>'错误的商家！']);
                }
                if(!$place['seller_id'] || !$place['seller'] || !isset($place['seller']['id'])){
                    return $this->error(['desc'=>'该地点尚未被商家绑定，无法完成转账！']);
                }
                if(model('seller')->where('id',$place['seller_id'])->setDec('money',$num)){
                    return $this->success([]);
                }else{
                    return $this->error(['desc'=>'转账失败！']);
                }
                break;
            case '103':
                if(model('seller')->where('id',$id)->setDec('money',$num)){
                    return $this->success([]);
                }else{
                    return $this->error(['desc'=>'转账失败！']);
                }
                break;
            default:
                return $this->error(['desc'=>'错误的类型！']);
        }
    }

}