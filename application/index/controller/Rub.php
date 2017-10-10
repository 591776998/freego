<?php
namespace app\index\controller;
class Rub extends \app\common\controller\Base
{

    //同意或拒绝蹭车的请求
    public function update(){
        if(
            !$this->check('id',['number','r','min:0'])||
            !$this->check('status',['number','r','min:0'])
        ){
            return error($this->checkError());
        }
        if(!$this->user){
            return error(['desc'=>'请登录后再操作！']);
        }

        $artRubCar = model('artRubCar')->with(['artJoin','art'])->where('id',$this->param('id'))->find();
        if(!$artRubCar){
            return error(['desc'=>'没找到蹭车记录！']);
        }
        $actJoin = $artRubCar['art_join'];
        if(!$actJoin){
            return error(['desc'=>'没找到参加记录！']);
        }

        //找到活动
        $activity = $artRubCar['art'];
        if(!$activity){
            return error(['desc'=>'没找到该活动！']);
        }

        if($this->user['id'] != $actJoin['user_id']){
            return error(['desc'=>'您不是被申请人！']);
        }

        $status = $this->param('status');
        if(101 == $status || 100 == $status){
            if(102 != $artRubCar['status']){
                switch ($artRubCar['status']){
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
                if(101 == $status){
                    if(($actJoin['total_seats'] - $actJoin['use_seats']) < $artRubCar['seats_num']){
                        return error(['desc'=>'剩余座位数不足！']);
                    }
                }
                $artRubCar->status = $status;
                if($artRubCar->save()){
                    //#消息的状态 101待处理 102已经同意 103未同意
                    model('msgSession')->where('session_type','102')->where('session_sub_type','102')->where('action_id',$this->param('id'))->data(['action_status'=>(101 == $status)?'102':'103'])->update();
                    $order = model('order')->where('id',$actJoin['order_id'])->find();
                    if(101 == $status){
                        if($order && ((0 != $order['pay_time']) && (0 == $order['refund_time']) && (0 == $order['use_time']))){
                            //把订单设置成已使用，然后给发起者转账
                            if($order->where('id',$order['id'])->where('use_time',0)->update([
                                'use_time'  => floor(THINK_START_TIME)
                            ])){
                                //活动发起者的管理费用
                                $b_price = $order['total_price'] * 0.1;
                                //被蹭车的人的收入
                                $a_price = $order['total_price'] - $b_price;

                                if(!model('money','service')->add($a_price,$order['seller_id'],$order['seller_type'])){
                                    return error(['desc'=>'转账失败！']);
                                }

                                //转10%给活动发起者
                                if(!model('money','service')->add($b_price,$activity['user_id'],101)){
                                    return error(['desc'=>'转账失败！']);
                                }

                            }
                        }

                        //减去座位数
                        model('artJoin')->where('id',$artRubCar['join_id'])->setInc('use_seats',$artRubCar['seats_num']);

                        //给蹭车者发消息
                        $msg = model('msg','service')
                            ->type(101, 107)
                            ->sender( $actJoin['user_id'],101)
                            ->target( $artRubCar['user_id'], 101)
                            ->action( false, $artRubCar['order_id'])
                            ->content('','已经同意您蹭车')
                            ->send()
                            ->push('您的蹭车申请通过了','蹭车成功');
                    }elseif(100 == $status){
                        if($order && ((0 != $order['pay_time']) && (0 == $order['refund_time']) && (0 == $order['use_time']))){
                            //把订单设置成已退款
                            if($order->where('id',$order['id'])->where('refund_time',0)->update([
                                'refund_time'  => floor(THINK_START_TIME)
                            ])){
                                if(!model('money','service')->add($order['total_price'],$order['user_id'],101)){
                                    return error(['desc'=>'退款失败！']);
                                }
                            }
                        }

                        //给蹭车者发消息
                        $msg = model('msg','service')
                            ->type(101, 107)
                            ->sender( $actJoin['user_id'],101)
                            ->target( $artRubCar['user_id'], 101)
                            ->action( false, $artRubCar['order_id'])
                            ->content('','未通过您蹭车')
                            ->send()
                            ->push('您的蹭车申请没有通过','蹭车失败');
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