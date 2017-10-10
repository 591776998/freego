<?php
namespace app\index\controller;
class Pay extends \app\common\controller\Base
{
    //创建支付
    public function save(){

        //1.输入订单号，输入金额，输入用户信息
        if(!$this->check('out_trade_no',['string','r'])){
            return error(['desc'=>'请输入订单号！']);
        }
        if(!$this->check('money',['number','r'])){
            return error(['desc'=>'请输入总金额！']);
        }
        if(!$this->check('rand',['string','r'])||
            !$this->check('check_sign',['string','r'])
        ){
            return error(['desc'=>'支付参数错误！']);
        }

        $money    = $this->param('money','0');
        $order_no = ((false !== stripos($this->param('out_trade_no',''),'R'))?strstr( $this->param('out_trade_no',''), 'R', true):$this->param('out_trade_no',''));
        $rand     = $this->param('rand','');
        $sign     = $this->param('check_sign','');

        if(!$this->checkShidianApp([$order_no,$money,$rand],$sign)){
            return error(['desc'=>'支付信息错误！']);
        }

        $par = [
            'trade_no'      => 'shidian_'.mt_rand(1,999999).'_'.floor(THINK_START_TIME*1000).'_'.mt_rand(1,999999),
            'order_no'      => $order_no,
            'money'         => $money,
            'type'          => 'shidian',
        ];

        //判断如果金额等于0的时候，查询出对应金额
        $order = model('Order')->where('order_no',$order_no)->find();
        if (!$order)
        {
            return error(['desc'=>'未找到该订单！']);
        }
        if(0 == $money){
            $money = $order['total_price'];
        }
        if(0 >= $money){
            return error(['desc'=>'支付金额错误！']);
        }

        if(!$this->user){
            return error(['desc'=>'请登录后再继续操作'],'请登录后再继续操作','401');
        }
        if($this->user['money'] < $par['money']){
            return error(['desc'=>'余额不足，请选择其他支付方式！']);
        }

        $r = model('money','service')->del($par['money'], $this->user['id'], 101);
        if($this->isError($r)){
            return error($r);
        }

        //设置输入参数
        $c = controller('index/Callback');
        $c->setParam($par);
        return $c->index();
    }


    //参数验证
    protected function checkShidianApp($data = [],$sign = ''){
        $data[] = config('pay_secret');
        $s = implode('',$data);
        if($s && (strtoupper(md5($s)) === strtoupper($sign))){
            return true;
        }
        return false;
    }
}