<?php
namespace app\seller\controller;
class Order extends \app\common\controller\SellerBase
{
    public function index()
    {
        if(!$this->seller)return $this->errorLogin();
        $seller_id = $this->seller['id'];
        $place_id  = $this->seller['place_id'];

        $type = $this->param('type');
        $p = $this->param('page',1);
        $ps = $this->param('page_size',10);
        $key_word = trim($this->param('key_word',''));

        $data = model('order')
            ->with(['orderItem','user']);
            if($place_id){
                $data->where(function ($query)use($place_id,$seller_id){
                    return $query->where(function ($query)use($seller_id){
                        return $query->where('seller_id',$seller_id)->where('seller_type',103);
                    })->whereOr(function ($query)use($place_id){
                        return $query->where('seller_id',$place_id)->where('seller_type',102);
                    });
                });
            }else{
                $data->where('seller_id',$seller_id)->where('seller_type',103);
            }

        if($key_word){
            $data->where('title','like','%'.$key_word.'%');
        }

        if(101 == $type){
            //待确认，未付款
            $data->where('status',102)->where('pay_time',0);
        }elseif(102 == $type){
            //已支付，未使用，未退款
            $data->where('pay_time','<>','0')->where('use_time','0')->where('refund_time','0');
        }elseif (103 == $type){
            //未付款，支持到店支付 订单状态 99创建中 100不可预定 101已确认  102待确认
            $data->where('pay_time',0)->where('refund_time','0')->where('status',101);
        }elseif(104 == $type){
            //已使用
            $data->where('use_time','<>','0');
        }

        $data = $data->page($p,$ps)->order('pay_time desc')->order('id desc');

        return success(array_map(function($d)use($type){
            $d['type']              = $type;
            $d['order_item']        =  array_map(function ($item){
                switch ($item['time_type']){
                    case '101':
                        $time_str = $this->formatTimeStr($item['s_time']).' 至 '.$this->formatTimeStr($item['e_time']);
                        break;
                    case '102':
                        $time_str = $this->formatTimeStr($item['s_date'],2).' 至 '.$this->formatTimeStr($item['e_date'],2);
                        break;
                    case '103':
                        $time_str = $this->formatTimeStr($item['u_date'],2);
                        break;
                    default:
                        $time_str = '';
                }
                $item['time_str'] = $time_str;
                //预定状态0无需预约 101未开始 102预定中 103预定成功 104预定失败
                $item['book_status_str'] = $this->formatBookStatus($item['book_status']);
                $item['ftof_str']        = (101 == $item['ftof'])?'到店支付':'';
                return $item;
            },$d['order_item']);
            return $d;
        },$data->select()));
    }

    public function update(){
        if(!$this->seller)return $this->errorLogin();
        if(!$this->check('id',['number','r','min:0'])){
            return error(['desc'=>'订单ID错误']);
        }
        $order = model('Order')->with(['orderItem'])->where('id',$this->param('id',0))->find();
        if(!$order){
            return error(['desc'=>'未找到该订单']);
        }

        $push = 0;

        $save = [];
        $status = $this->param('status',0);
        if($status){
            if($status != $order['status']){
                //如果是确认订单
                if(102 == $order['status']){
                    if(in_array($status,[101,100])){
                        $save['status'] = $status;
                        if(101 == $status){
                            $exp_time = isset($save['exp_time'])?$save['exp_time']:1800;
                            if(60 > $exp_time)$exp_time = 60;
                            $exp_time += time();
                            $save['exp_time'] = $exp_time;
                            $push = 101;
                        }elseif (100 == $status){
                            $push = 100;
                        }
                    }else{
                        return error(['desc'=>'错误的status']);
                    }
                }elseif(101 == $order['status']){
                    return error(['desc'=>'该订单已经可以预定']);
                }elseif (100 == $order['status']){
                    return error(['desc'=>'该订单已经不可预定']);
                }
            }
        }
        if($this->has('use_time')){
            $save['use_time'] = floor(THINK_START_TIME);
        }

        if($save){
            if(model('order')->where('id',$order['id'])->update($save)){

                if($push){

                    $msg = new \app\index\service\Msg();
                    $msg->type(101, 100)
                        ->sender($order['seller_id'],$order['seller_type'])
                        ->target($order['user_id'], 101)
                        ->content('您的订单已预定，请继续完成付款', $order['title'])
                        ->send()
                        ->push();
                    //todo 这里要发短信
//                    model('sms','service')->template()->phone()->
                }

                return success(model('order')->with(['orderItem'])->where('id',$order['id'])->find());
            }else{
                return error(['desc'=>'修改失败']);
            }
        }else{
            return error(['desc'=>'没有做任何修改']);
        }
    }

    public function show(){
        if(!$this->seller){
            return $this->errorLogin();
        }
        $code = trim($this->param('code',''));
        $order_no = trim($this->param('order_no',''));
        if($code){
            $o = model('order')->where('verify_code',$code)->find();
        }elseif ($order_no){
            $o = model('order')->where('order_no',$order_no)->find();
        }else{
            return error(['desc'=>'请输入正确的信息']);
        }

        if($o){
            //101user用户 102place地点 103seller商家用户
            if((102 == $o['seller_type']) && ($o['seller_id'] != $this->seller['place_id'])){
                return error(['desc'=>'没有找到该订单，请确认输入正确']);
            }elseif((103 == $o['seller_type']) && ($o['seller_id'] != $this->seller['id'])){
                return error(['desc'=>'没有找到该订单，请确认输入正确']);
            }

            $id = $o['id'];
        }else{
            return error(['desc'=>'未找到该订单，请确认输入正确']);
        }
        $service = new \app\index\service\Order();
        $order = $service->read([],$id);
        return success($order);
    }

    //格式化预定状态
    protected function formatBookStatus($book_status = 0){
        switch ($book_status){
            case '0':
                return '';
            case '101':
            case '102':
                return '预定中';
                break;
            case '103':
                return '预定成功';
                break;
            case '104':
                return '预定失败';
            default:
                return '';
        }
    }

    //格式化时间输出 $level 1日期时间 2日期
    protected function formatTimeStr($time,$level = 1){
        return formatTimeStr($time,$level);
    }

}