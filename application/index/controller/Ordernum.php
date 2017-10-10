<?php
namespace app\index\controller;
class Ordernum extends \app\common\controller\Base
{
    //
    public function read(){
        if(!$this->user){
            return error(['desc'=> '请先登录！'],'请先登录！','401');
        }
        if($this->user['id'] != $this->param('id')){
            return error(['desc'=> '只能查看自己的订单数！']);
        }

        //未支付且未过期的
        $order_num['unpay']     = model('order')->where('user_id',$this->user['id'])->where('comment_time','0')->where('pay_time','0')->where('exp_time','>',floor(THINK_START_TIME))->count().'';
        $order_num['uncomment'] = model('order')->where('user_id',$this->user['id'])->where('comment_time','0')->where('pay_time','<>','0')->count().'';
        $order_num['complete']  = '0';
        return success($order_num);
    }


}