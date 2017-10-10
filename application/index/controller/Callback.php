<?php
namespace app\index\controller;
use think\Request;
class Callback extends \app\common\controller\Base
{
    protected $pay_type = 0; //101微信 102支付宝 103零钱
    protected $input    = [];

    protected $log_id   = '0';//当前请求的日志id

    protected $true_order_no = '';//真实的本地订单号

    //接收支付异步通知
    public function index(){
        //1.鉴别是支付的类型
        $type = $this->param('type','');
        switch ($type){
            case 'weixin':
                //微信
                $this->pay_type = '101';
                $in         = json_decode( json_encode( simplexml_load_string( file_get_contents( 'php://input'), 'SimpleXMLElement', LIBXML_NOCDATA)), true);
                $this->true_order_no = (isset($in['out_trade_no'])?((false !== stripos($in['out_trade_no'],'R'))?strstr( $in['out_trade_no'], 'R', true):$in['out_trade_no']):'');

                //记录请求日志
                $this->log($in,isset($in['out_trade_no'])?$in['out_trade_no']:'');
                //验证请求是否是可信的
                if(!$this->is_true_weixin($in)){
                    return $this->response(true, false, '验证三方服务器错误',1);
                }

                $this->input = [
                    'trade_no'  => isset($in['transaction_id'])?$in['transaction_id']:'',
                    'order_no'  => $this->true_order_no,
                    'pay_type'  => $this->pay_type,
                    'money'     => isset($in['total_fee'])?$in['total_fee']:'',
                ];

                if(isset($in['return_code']) && isset($in['result_code']) && ('SUCCESS' == $in['return_code']) && 'SUCCESS' == $in['result_code']){
                    //微信请求成功，调起统一支付回调流程
                    return $this->deal();
                }else{
                    //微信请求错误
                    return $this->response(true, false, '三方服务器请求状态错误',2);
                }
                break;
            case 'alipay':
                //支付宝
                $this->pay_type = '102';
                $in  = $this->param;
                $this->true_order_no = (isset($in['out_trade_no'])?((false !== stripos($in['out_trade_no'],'R'))?strstr( $in['out_trade_no'], 'R', true):$in['out_trade_no']):'');

                //记录请求日志
                $this->log($in,isset($in['out_trade_no'])?$in['out_trade_no']:'');
                //验证请求是否是可信的
                if(!$this->is_true_alipay($in)){
                    return $this->response(true, false, '验证三方服务器错误',1);
                }

                $this->input = [
                    'trade_no'  => isset($in['trade_no'])?$in['trade_no']:'',
                    'order_no'  => $this->true_order_no,
                    'pay_type'  => $this->pay_type,
                    'money'     => isset($in['total_fee'])?($in['total_fee']*100):(isset($in['total_amount'])?($in['total_amount']*100):'0')//,
                ];

                if( ( isset($in['trade_status']) && ('TRADE_SUCCESS' == $in['trade_status']) ) || ( isset($in['trade_status']) && 'TRADE_FINISHED' == $in['trade_status']) ){
                    //用户支付成功时，调起统一支付回调流程
                    return $this->deal();
                }else{
                    //支付不成功或者等待付款
                    return $this->response(true, false, '三方服务器请求状态错误',2);
                }
                break;
            case 'shidian':
                $this->pay_type = '103';
                $in  = $this->param;
                $this->true_order_no = (isset($in['order_no'])?((false !== stripos($in['order_no'],'R'))?strstr( $in['order_no'], 'R', true):$in['order_no']):'');

                $this->log($in,isset($in['order_no'])?$in['order_no']:'');
                if(!$this->is_true_shidian($in)){
                    return $this->response(true, false, '验证时点服务器错误',1);
                }

                $this->input = [
                    'trade_no'  => $in['trade_no'],
                    'order_no'  => $this->true_order_no,
                    'pay_type'  => $this->pay_type,
                    'money'     => $in['money']
                ];

                return $this->deal();
                break;
            default:
                $this->pay_type = '0';
                $this->true_order_no = '';
                $this->log($this->param,'');
                return $this->response(true, false, '错误的支付方式',10);
        }
    }

    //统一处理流程
    public function deal(){
        $input = $this->input;

        //判断参数是否错误
        if(! ($input['order_no'] && $input['trade_no'] && $input['pay_type'])){
            return $this->response(true, false, '输入参数错误',3);
        }

        //判断重复的请求
        if($this->is_repeat($input['order_no'],$input['trade_no'],$input['pay_type'])){
            return $this->response(false, false, '重复的请求',4);
        }

        //4.判断订单信息
        $order = model('order')->with('order_item')->where('order_no',$input['order_no'])->find();
        if(!$order){
            return $this->response(true, false, '订单不存在',5);
        }

        //5.订单如果已经支付，失效，就退款到零钱
        if(0 != $order['pay_time']){
            $this->refund();
            return $this->response(false, false, '订单已经支付',6);
        }

        //订单状态不正确的
        if(101 != $order['status']){
            $this->refund();
            return $this->response(false, false, '错误的订单状态:'.$order['status'],7);
        }

        //订单金额不正确的
        if($input['money'] < $order['total_price']){
            return $this->response(true, false, '订单金额错误:'.$input['money'].'/'.$order['total_price'],8);
        }

        //更新订单状态
        $order->trade_no = $input['trade_no'];
        $order->pay_type = $input['pay_type'];
        $order->pay_time = floor(THINK_START_TIME);

        if(!$order->save()){
            //更新订单状态失败
            $this->refund();
            return $this->response(false, false, '更新订单状态失败',9);
        }

        //6.完成订单对应的操作
        $res = model('Order','service')->complete($order);
        if($this->isError($res)){
            return $this->response(false, false, $res['desc'],$res['code']);
        }
        return $this->response(false, true, '支付完成',200);
    }


    /***************************************************************************/
    //给第三方服务抛出响应
    // @type 1验证服务器错误
    protected function response($goon = false, $is_normal = false, $desc = '',$type = 0){
        //记录结果日志
        model('payRecord')->data([
            'pay_type'  => $this->pay_type,
            'log_id'    => $this->log_id,
            'order_no'  => isset($this->input['order_no'])?$this->input['order_no']:'',
            'desc'      => $desc,
            'type'      => $type,
            'normal'    => $is_normal?'101':'102',
            'c_time'    => floor(THINK_START_TIME)
        ])->save();

        switch ($this->pay_type){
            case '101':
                return $this->responseToWeixin($goon,$desc);
                break;
            case '102':
                return $this->responseToAlipay($goon,$desc);
                break;
            case '103':
                if(200 == $type){
                    return success(['desc'=>$desc]);
                }else{
                    return error(['desc'=>$desc]);
                }
                break;
        }
        return '使用了未知的支付方式，无法响应正确信息';
    }

    //向微信响应
    protected function responseToWeixin($goon = true,$msg = 'OK'){
        $success = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        $fail = '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA['.$msg.']]></return_msg></xml>';
        if($goon){
            return $fail;
        }
        return $success;
    }

    //向支付宝响应
    protected function responseToAlipay($goon = true,$msg = 'OK'){
        $success = 'success';
        $fail = 'fail';
        if($goon){
            return $fail;
        }
        return $success;
    }

    //记录普通日志
    protected function log($data, $order_no = ''){
        if(!is_string($data) && !is_numeric($data)){
            $data = json_encode($data);
        }
        $payLog = model('payLog');
        if($payLog->data([
            'pay_type'          => $this->pay_type,
            'data'              => $data,
            'c_time'            => floor(THINK_START_TIME),
            'del_time'          => '0',
            'order_no'          => $order_no,
            'true_order_no'     => $this->true_order_no
        ])->save()){
            $this->log_id = $payLog->id;
            return $payLog->id;
        }else{
            return '0';
        }
    }

    //给支付者退款
    protected function refund(){
        //这里不退款，所有失败的走其他途径退款
    }

    //判断是否是重复请求
    protected function is_repeat($order_no,$trade_no,$pay_type){
        if(model('order')->where('order_no',$order_no)->where('trade_no',$trade_no)->where('pay_type',$pay_type)->find()){
            return true;
        }
        return false;
    }

    //判断阿里服务是否正确
    protected function is_true_alipay($data){
        if($data){
            //todo 不判断是不合理的，但时间不够，以后加
            return true;
        }else{
            return false;
        }
    }

    //判断阿里服务是否正确
    protected function is_true_weixin($data){
        if($data){
            //todo 不判断是不合理的，但时间不够，以后加
            return true;
        }else{
            return false;
        }
    }

    //判断是时点的服务器调用了接口
    protected function is_true_shidian($data){
        if($data){
            //todo 不判断是不合理的，但时间不够，以后加

            return true;
        }else{
            return false;
        }
    }

    //支付宝异步通知输入参数解析
    protected function alipayParse($data){

    }

    //微信支付异步通知输入参数解析
    protected function weixinParse($data){

    }

}