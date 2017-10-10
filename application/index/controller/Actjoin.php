<?php
namespace app\index\controller;
use think\Request;
class Actjoin extends \app\common\controller\Base
{
    //参加活动
    public function save(){
        if(!$this->check('id',['number','r','min:0'])){
            return error($this->checkError());
        }


        return success(
        );

    }

    //同意或拒绝参加活动的请求
    public function update(){
        if(
            !$this->check('id',['number','min:0'])||
            !$this->check('status',['number','min:0'])
        ){
            return error($this->checkError());
        }
        if(!$this->user){
            return error(['desc'=>'请登录后再操作！']);
        }

        $actJoin = model('artJoin')->where('id',$this->param('id'))->find();
        if(!$actJoin){
            return error(['desc'=>'没找到参加记录！']);
        }
        $act = model('art')->with(['act'])->where('id',$actJoin['main_id'])->find();
        if(!$act){
            return error(['desc'=>'没找到该活动！']);
        }
        if($this->user['id'] != $act['user_id']){
            return error(['desc'=>'您不是活动的发起者！']);
        }
        //判断给钱了没有，给钱了的话就修改
        $status = $this->param('status');
        if(101 == $status || 100 == $status){
            if(102 != $actJoin['status']){
                switch ($actJoin['status']){
                    case '100':
                        return error(['desc'=>'该申请已被拒绝！']);
                        break;
                    case '101':
                        return error(['desc'=>'该申请已经通过了！']);
                        break;
                    case '103':
                        return error(['desc'=>'该申请尚未支付！']);
                        break;
                    default:
                        return error(['desc'=>'该申请状态错误！']);
                }
            }else{
                $actJoin->status = $status;
                if($actJoin->save()){
                    //设置消息的状态
                    model('msgSession')->where('session_type','102')->where('session_sub_type','103')->where('action_id',$this->param('id'))->update(['action_status'=>(101 == $status)?'102':'103']);
                    $order = model('order')->with(['orderItem'])->where('id',$actJoin['order_id'])->find();
                    if(101 == $status){
                        if($order && ((0 != $order['pay_time']) && (0 == $order['refund_time']) && (0 == $order['use_time']))){
                            //把订单设置成已使用，然后给发起者转账
                            if($order->where('id',$order['id'])->where('use_time',0)->update([
                                'use_time'  => floor(THINK_START_TIME)
                            ])){
                                if(!model('money','service')->add($order['total_price'],$order['seller_id'],$order['seller_type'])){
                                    return error(['desc'=>'转账失败！']);
                                }
                            }else{
                                return error(['desc'=>'设置失败']);
                            }
                        }

                        //减去库存
                        if(isset($data['order_item']) && $data['order_item']){
                            foreach ($data['order_item'] as $vo){
                                model('plan')->where('id',$vo['plan_id'])->setDec('stock',$vo['num']);
                            }
                        }

                        //处理参加人数和车位数
                        if($actJoin['total_seats']){
                            model('art')->where('id',$actJoin['main_id'])->data(['joined_count'=>'`joined_count`+1','car_count'=>'`car_count`+'.$actJoin['total_seats']])->update();
                        }else{
                            model('art')->where('id',$actJoin['main_id'])->setInc('joined_count');
                        }

                        //加入群组
                        if(isset($act['act']['tid']) && $act['act']['tid']){
                            $group_msg = '欢迎加入';
                            $netease = model('Netease','service')->request('add_member',['tid'=>$act['act']['tid'],'owner'=>$act['user_id'],'members'=>json_encode([$actJoin['user_id']]),'magree'=>'0','msg'=>$group_msg]);
                        }

                        //给参加者发消息、
                        $msg = model('msg','service')
                            ->type(101, 107)
                            ->sender( $act['user_id'],101)
                            ->target( $actJoin['user_id'], 101)
                            ->action( false, $act['id'])
                            ->content('','您已成功加入'.$act['title'].'活动')
                            ->send()
                            ->push();
                    }elseif(100 == $status){
                        //订单设置成已退款，并给申请人退钱
                        if($order && ((0 != $order['pay_time']) && (0 == $order['refund_time']) && (0 == $order['use_time']))){
                            //把订单设置成已退款，然后退钱
                            if($order->where('id',$order['id'])->where('refund_time',0)->update([
                                'refund_time'  => floor(THINK_START_TIME)
                            ])){
                                if(!model('money','service')->add($order['total_price'],$order['user_id'],101)){
                                    return error(['desc'=>'退款失败！']);
                                }
                            }else{
                                return error(['desc'=>'设置失败']);
                            }
                        }
                        $msg = model('msg','service')
                            ->type(101, 107)
                            ->sender( $act['user_id'],101)
                            ->target( $order['user_id'], 101)
                            ->action( false, $act['id'])
                            ->content('','参加'.$act['title'].'活动失败，费用已退还到零钱')
                            ->send()
                            ->push();
                    }

                   return success($actJoin);
                }else{
                    return error(['desc'=>"操作失败！请稍后再试！"]);
                }
            }
        }else{
            return error(['desc'=>'当前只支持同意或拒绝操作！']);
        }
    }

}