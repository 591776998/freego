<?php
namespace app\index\service;
class Order extends \app\common\service\Base
{

    //订单列表
    public function index( $user, $type, $p = 1, $ps = 10){
        if(!$user){
            return [];
        }

        $order = model('order')->with('order_item')
        ->where('user_id',$user['id'])->where('status','<>','98');
        if($type){
            switch ($type){
                case '101':
                    //未付款的，没有过期时间的，过期时间未到的
                    $order->where('pay_time','0')->where(function ($query){
                        $query->where('exp_time','>',floor(THINK_START_TIME))->whereOr('exp_time','0');
                    });
                    break;
                case '102':
                    $order->where('pay_time','<>','0')->where('comment_time','0');
                    break;
                case '103':
                    $order->where('pay_time','<>','0')->where('comment_time','<>','0');
                    break;
            }
        }

        return array_map(function($vo)use($type){
            $vo = $vo->toArray();
//            $d['pay_info']          = $this->payInfo($d['order_no'],$d['total_price'],$d['title'],$d['title']);
            $vo['type']              = $type;
//            $d['c_time_str']        = '订单于'.$this->formatTimeStr($d['c_time']).'生成';
//            $d['pay_time_str']      = $d['pay_time']?'订单于'.$this->formatTimeStr($d['pay_time']).'完成支付':'';
//            $d['comment_time_str']  = $d['comment_time']?'订单于'.$this->formatTimeStr($d['comment_time']).'完成点评':'';
//
//            $d['time_str_arr']      = [];
//            $d['time_str_arr'][]    = '订单于'.$this->formatTimeStr($d['c_time']).'生成';
//            if($d['pay_time'])$d['time_str_arr'][] = '订单于'.$this->formatTimeStr($d['pay_time']).'完成支付';
//            if($d['comment_time'])$d['time_str_arr'][] = '订单于'.$this->formatTimeStr($d['comment_time']).'完成点评';


            if(100 == $vo['status']){
                $vo['book_status_str'] = '预定失败';
            }elseif (101 == $vo['status']){
                $vo['book_status_str'] = '预定成功';
            }elseif (102 == $vo['status']){
                $vo['book_status_str'] = '预定中';
            }else{
                $vo['book_status_str'] = '';
            }
            $vo['ftof_str']        = (101 == $vo['ftof'])?'到店支付':'';

            $vo['order_item']      = array_map($this->formatOrderItem(),$vo['order_item']);

            $vo['main_type']       = '';
            $vo['main_id']         = '';

            if(103 == $vo['order_type']){
                $vo['main_type']    = '112';
                $vo['main_id']      = $vo['seller_id'];
            }elseif (102 ==  $vo['order_type']) {
                $vo['main_type'] = '103';
                $vo['main_id'] = $vo['activity_id'];
            }
            else{
                $vo['main_type'] = '104';
                $vo['main_id'] = $vo['seller_id'];
            }
            return $vo;
        },$order->order('id desc')->page($p,$ps)->select());
    }

    //格式化输出 订单子项
    protected function formatOrderItem(){
        return function ($item){
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
        };
    }

    //格式化时间输出 $level 1日期时间 2日期
    protected function formatTimeStr($time,$level = 1){
        return formatTimeStr($time,$level);
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

    public function read($user,$id){
        $order = model('order')->with('order_item')
            ->where('id',$id)->find();
        if($order){
            $order = $order->toArray();
//            $order['pay_info']          = $this->payInfo($order['order_no'],$order['total_price'],$order['title'],$order['title']);
            $order['order_item']        = array_map($this->formatOrderItem(),$order['order_item']);
            $order['services_id_arr']    = [];
            foreach ($order['order_item'] as $item){
                if($item['services_id'] && !in_array($item['services_id'],$order['services_id_arr'])){
                    $order['services_id_arr'][] = $item['services_id'];
                }
            }
//            $order['c_time_str']        = '订单于'.$this->formatTimeStr($order['c_time']).'生成';
//            $order['pay_time_str']      = $order['pay_time']?'订单于'.$this->formatTimeStr($order['pay_time']).'完成支付':'';
//            $order['comment_time_str']  = $order['comment_time']?'订单于'.$this->formatTimeStr($order['comment_time']).'完成点评':'';

            $order['time_str_arr']          = [];
            $order['time_str_arr'][]        = '订单于'.$this->formatTimeStr($order['c_time']).'生成';
            if($order['pay_time'])$order['time_str_arr'][]     = '订单于'.$this->formatTimeStr($order['pay_time']).'完成支付';
            if($order['comment_time'])$order['time_str_arr'][] = '订单于'.$this->formatTimeStr($order['comment_time']).'完成点评';

            if($order['comment_time'] && $order['pay_time']){
                $order['type'] = '103';
            }else if($order['pay_time']) {
                $order['type'] = '102';
            }else{
                $order['type'] = '101';
            }
            //判断订单的状态
            $order['order_status'] = $this->order_status($order);
        }
        return $order;
    }

    //判断订单的状态
    public function order_status($data){
        //1.预定中 2。立即支付 3.订单过期或失效 6.立即点评 7.点评完成
        if($data['comment_time']){
            return '7';
        }else{
            if($data['pay_time']){
                //已支付
                return '6';
            }else{
                $pay_time = config('pay_time');//支付的最小过期时间
                //未支付
                if($data['exp_time'] && $data['exp_time'] < (floor(THINK_START_TIME) + $pay_time)){
                    return '3';
                }
                if(101 != $data['status']){
                    return '1';
                }
                return '2';
            }
        }
    }

    //1.创建订单
    public function save($user, $type, $main_data ,$plan = []){
        //todo 下次记得使用多态
        if(!$user){
            return $this->error(['desc'=>'请登录再进行下一步操作']);
        }
        $percentage = 0;
        $discount   = 10000;
        switch ($type){
            case '100':
                //扫码消费
                $place = model('place')->where('id',$main_data['seller_id'])->find();
                if(!$place){
                    return $this->error(['desc'=>'没找到该商家，请稍后再试！']);
                }
                if(!$place['seller_id']){
                    return $this->error(['desc'=>'商家信息错误，请稍后再试！']);
                }
                $percentage = $place['percentage'];
                $discount   = $place['discount'];
                $main_data['seller_id'] = $place['id'];
                $main_data['seller_type'] = '102';
                $main_title = $place['title'];
                $plan_arr = [
                    $this->planObjectData([
                        'num'       => $main_data['money'],
                        'title'     => '扫码消费',
                        'item_type' => '100'
                    ]),
                ];
                $plan_arr = $this->mergePlanData([['id'=>'0']],$plan_arr);
                break;
            case '101':
                //购买服务和商品
                $services = model('services')->where('id',$main_data['services_id'])->with(['plan'])->find();
                if(!$services){
                    return $this->error(['desc'=>'该服务不存在！']);
                }
                if(!$services->plan){
                    return $this->error(['desc'=>'套餐不存在！']);
                }
                $place = model('place')->where('id',$services['place_id'])->find();
                if(!$place){
                    return $this->error(['desc'=>'没找到该商家，请稍后再试！']);
                }
                if(!$place['seller_id']){
                    return $this->error(['desc'=>'商家信息错误，请稍后再试！']);
                }

                $percentage = $place['percentage'];
                $main_data['seller_id']   = $place['id'];
                $main_data['seller_type'] = '102';
                $main_title = $services['title'];
                $plan_arr = $this->mergePlanData($plan,$services->plan);
                break;
            case '102':
                //参加活动
                $activity = model('art')->with(['plan'])->where('id',$main_data['art_id'])->find();
                if(!$activity){
                    return $this->error(['desc'=>'没找到该活动！']);
                }
                $main_data['seller_id']     = $activity['user_id'];
                $main_data['seller_type']   = '101';
                $main_data['activity_id']   = $activity['id'];
                $main_title = $activity['title'];
                $plan_arr = $this->mergePlanData($plan,$activity->plan);
                break;
            case '103':
                //蹭车
                $activity = model('art')->where('id',$main_data['art_id'])->find();
                if(!$activity){
                    return $this->error(['desc'=>'没找到该活动！']);
                }
                $join = model('artJoin')->where('id',$main_data['target_id'])->where('main_id',$main_data['art_id'])->where('status','101')->find();
                if(!$join){
                    return $this->error(['desc'=>'没找到该记录或该用户已退出活动！']);
                }
                //检查车位是否足够
                if(($join['total_seats'] - $join['use_seats']) < 1){
                    return $this->error(['desc'=>'剩余座位不足，您可以选择其他车！']);
                }
                // 判断用户是否蹭了其他车
                $my_rub = model('artRubCar')->where('art_id',$main_data['art_id'])->where('user_id',$user['id'])->where(function ($query) {
                    $query->where(function ($sub_query){
                        $sub_query->where('status',101);
                    })->whereOr(function ($sub_query){
                        $sub_query->where('status',102)->where('c_time','<',floor(THINK_START_TIME)-1800);
                    });
                })->find();
                if($my_rub){
                    if(101 == $my_rub['status']){
                        if($my_rub['join_id'] == $main_data['target_id']){
                            $error = '您已经蹭了该车了，请不要重复申请！';
                        }else{
                            $error = '您已经蹭了别人的车！';
                        }
                    }elseif (102 == $my_rub['status']){
                        if($my_rub['join_id'] == $main_data['target_id']){
                            $error = '您已经提交了申请，请不要重复提交！';
                        }else{
                            $error = '您已经申请了别人的车，要更换蹭车请先取消，或等待一段时间！';
                        }
                    }else{
                        $error = '不满足蹭车条件，请稍后再试！';
                    }
                    return $this->error(['desc'=>$error]);
                }
                //判断被蹭车人与蹭车人是否是同一人
                if($join['user_id'] == $user['id']){
                    return $this->error(['desc'=>'不能蹭自己的车！']);
                }

                $main_data['seller_id'] = $join['user_id'];
                $main_data['seller_type'] = '101';
                $main_data['activity_id']   = $activity['id'];
                $main_title = $activity['title'];
                $seat_num = 1;
                $p = $this->planObjectData([
                    'num'           => $seat_num,
                    'title'         => '蹭车费用',
                    'fact_price'    => $main_data['money'],
                    'use_points'    => $main_data['points'],
                    'item_type'     => '99',
                ]);
                $plan_arr = $this->mergePlanData([['id'=>'0']],[$p]);
                break;
            default:
                return $this->error(['desc'=>'错误的订单类型！']);
        }

        if($this->isError($plan_arr)){
            return $this->error($plan_arr);
        }

        if(!$plan_arr['data']){
            return $this->error(['desc'=>'请至少选择1个套餐']);
        }
        $plan_arr = $plan_arr['data'];
        //优惠前的总价
        $payable = floor(array_sum(array_column($plan_arr,'total_price')));

        //优惠后的总价
        $total_price = ceil($payable * ($discount/10000));

        //总共返给用户的积分
        $total_points = floor(array_sum(array_column($plan_arr,'total_points')));
        //总共花费的积分
        $total_use_points = floor(array_sum(array_column($plan_arr,'use_points')));

        $ftof = $this->has_ftof(array_column($plan_arr,'ftof'));
        $verify = $this->has_ftof(array_column($plan_arr,'verify'));
        $need_book = $this->has_ftof(array_column($plan_arr,'need_book'));

        $verify_code = '';
        $verify_expire = '0';

        if(101 == $verify){
            //添加验票码
            $verify_code = $this->getVerifyCode($main_data['seller_id'],$main_data['seller_type']);
            if(false === $verify_code){
                return $this->error(['desc'=>'创建订单失败了！']);
            }
            $verify_expire = (floor(THINK_START_TIME)+(config('verify_expire_time'))?:(86400*30*3));
        }

        if(0 > $percentage || 10000 < $percentage){
            return $this->error(['desc'=>'异常的分成比例']);
        }

        if($percentage){
            $profit = floor($total_price * ($percentage/10000));
        }else{
            $profit = 0;
        }
        if($profit < 0)$profit = 0;

        if(100 == $type)$main_title .= '扫码消费';
        $order_data = [
            'title'                 => $main_title,
            'user_id'               => $user['id'],
            'seller_id'             => $main_data['seller_id'],
            'seller_type'           => $main_data['seller_type'],
            'activity_id'           => isset($main_data['activity_id'])?$main_data['activity_id']:'0',
            'payable'               => $payable,
            'total_price'           => $total_price,
            'total_back_points'     => $total_points,
            'total_cons_points'     => $total_use_points,
            'need_book'             => $need_book,
            'ftof'                  => $ftof,
            'verify'                => $verify,
            'order_type'            => $type,
            'verify_code'           => $verify_code,
            'verify_expire'         => $verify_expire,
            'profit'                => $profit
        ];
        $order_info = $this->createOrder($order_data);
        if($this->isError($order_info)){
            return $this->error($order_info);
        }

        $sub_info = $this->createOrderItem($order_info['data']['id'],$plan_arr);
        if($this->isError($sub_info)){
            return $this->error($sub_info);
        }

        if(102 == $type){
            //添加活动参加表记录
            $art_join = model('artJoin');
            $art_join->data([
                'main_id'       => $main_data['art_id'],
                'order_id'      => $order_info['data']['id'],
                'user_id'       => $user['id'],
                'del_time'      => '0',
                'c_time'        => floor(THINK_START_TIME),
                'status'        => '103',
                'car_model_id'  => isset($main_data['car_model_id'])?$main_data['car_model_id']:'0',
                'car_name'      => isset($main_data['car_name'])?$main_data['car_name']:'',
                'total_seats'   => isset($main_data['total_seats'])?$main_data['total_seats']:'0',
                'use_seats'     => '0'
            ]);
            if(!$art_join->save()){
                model('order')->destroy($order_info['data']['id']);
                return $this->error(['desc'=>'活动参加信息错误！']);
            }
        }else if(103 == $type){
            //蹭车
            $rub = model('artRubCar');
            $rub->data([
                'user_id'   => $user['id'],
                'art_id'    => $main_data['art_id'],
                'join_id'   => $main_data['target_id'],
                'j_user_id' => $join['user_id'],
                'seats_num' => $seat_num,
                'order_id'  => $order_info['data']['id'],
                'status'    => '103',
                'content'   => isset($main_data['content'])?$main_data['content']:'',
                'money'     => $total_price,
                'points'    => $total_use_points,
                'c_time'    => floor(THINK_START_TIME),
                'del_time'  => '0'
            ]);
            if(!$rub->save()){
                model('order')->destroy($order_info['data']['id']);
                return $this->error(['desc'=>'蹭车信息错误！']);
            }
        }

        $order_info['data']['order_item'] = model('OrderItem')->where('order_id',$order_info['data']['id'])->select();
        //成功 返回支付信息

        $pay_info = $this->payInfo($order_info['data']['id']);
        if($this->isError($pay_info)){
            return $this->error($pay_info,$pay_info['desc'],$pay_info['code']);
        }
        $order_info['data']['pay_info'] = $pay_info['data'];
        return $this->success($order_info['data']);
    }

    //返回支付信息
    public function payInfo($id){
        $order = model('order')->where('id',$id)->find();
        if(!$order){
            return $this->error(['desc'=>'该订单不存在或者已过期']);
        }else{
            $pay_time = config('pay_time');//支付的最小过期时间

            if( $order['exp_time'] && (($order['exp_time'] - $pay_time) < floor(THINK_START_TIME))){
                return $this->error(['desc'=>'该订单已过期，请重新下单！']);
            }
            if( $order['pay_time']){
                return $this->error(['desc'=>'该订单已被支付！']);
            }
            if( 101 != $order['status']){
                if(102 == $order['status']){
                    return $this->error(['desc'=>'该商品需要商家确认是否还有，确认后将第一时间通知您'],'该商品需要商家确认是否还有，确认后将第一时间通知您','1064');
                }elseif (99 == $order['status']){
                    return $this->error(['desc'=>'该订单正在创建中']);
                }else{
                    return $this->error(['desc'=>'订单状态错误，请稍后再试！']);
                }
            }
            $time_expire = '1800';
            if($order['exp_time'])$time_expire = ($order['exp_time'] - floor(THINK_START_TIME)).'';
            return $this->success([
                'out_trade_no'      => $order['order_no'],
                'total_fee'         => $order['total_price'],
                'time_expire'       => $this->weixinExprieTime($time_expire),//微信过期时间
                'timeout_express'   => $this->alipayExprieTime($time_expire),//支付宝过期时间
                'body'              => $order['title'],
                'subject'           => $order['title'],
                'notify_url'        => web_url('Callback/index/type/weixin',[],true)
            ]);
        }
    }

    //把秒数转换成微信的过期时间参数
    protected function weixinExprieTime($time){
        return date('YmdHis',floor(THINK_START_TIME)+$time);
    }

    //把秒数转换成支付宝的过期时间参数
    protected function alipayExprieTime($time){
        return floor($time/60).'m';
    }

    //创建订单
    public function createOrder($data){
        if(!isset($data['seller_id']) ||!isset($data['seller_type']))return $this->error(['desc'=>'该商家不存在！']);
        if(!isset($data['user_id']))return $this->error(['desc'=>'用户信息错误！请确认登录状态！']);
        if(!isset($data['total_price']))return $this->error(['desc'=>'价格计算错误！']);
        if(!isset($data['total_back_points']))return $this->error(['desc'=>'积分计算错误！']);
        if(!isset($data['total_cons_points']))return $this->error(['desc'=>'积分计算错误！']);
        if(!isset($data['need_book']))return $this->error(['desc'=>'创建订单错误！']);
        if(!isset($data['ftof']))return $this->error(['desc'=>'创建订单错误！']);
        if(!isset($data['verify']))return $this->error(['desc'=>'创建订单错误！']);
        $model = model('order')->data([
            'title'             => $data['title'],
            'order_no'          => $this->createOrderNoPre($data['seller_id'],$data['seller_type']),
            'user_id'           => $data['user_id'],
            'seller_id'         => $data['seller_id'],
            'seller_type'       => $data['seller_type'],
            'order_type'        => $data['order_type'],
            'total_price'       => $data['total_price'],
            'total_back_points' => $data['total_back_points'],
            'total_cons_points' => $data['total_cons_points'],
            'c_time'            => floor(THINK_START_TIME),
            'del_time'          => '0',
            'pay_time'          => '0',
            'action_lock'       => '0',
            'status'            => '99',
            'ftof'              => $data['ftof'],
            'verify'            => $data['verify'],
            'activity_id'       => isset($data['activity_id'])?$data['activity_id']:'0',
            'verify_code'       => isset($data['verify_code'])?$data['verify_code']:'',
            'verify_expire'     => isset($data['verify_expire'])?$data['verify_expire']:'0',
            'profit'            => isset($data['profit'])?$data['profit']:'0',
        ]);
        if($model->save()){
            //查询在当天早于该订单的订单有几个
            $today_count = model('order')->where('seller_id',$data['seller_id'])->where('seller_type',$data['seller_type'])->where('c_time','>',strtotime(date('Y-m-d')))->where('id','<=',$model->id)->count();
            $model->order_no = $model->order_no.(formatNumTo($today_count+1,6));
            $model->status    = (101 == $data['need_book'])?102:101;
            if($model->save()){
                return $this->success($model);
            }else{
                $model->destroy($model->id);
                return $this->error(['desc'=>'创建订单失败，请稍后再试！']);
            }
        }else{
            return $this->error(['desc'=>'创建订单失败，请稍后再试！']);
        }
    }

    //批量创建订单子项
    public function createOrderItem($order_id, $data){
        foreach ($data as $k => $v){
            if(!isset($v['plan_type']))return $this->error(['desc'=>'订单项错误，创建订单失败！']);
            switch ($v['plan_type']){
                case '101':
                case '102':
                    if(!isset($v['id']))return $this->error(['desc'=>'错误的套餐，创建订单失败！']);
                    break;
            }
            if(!isset($v['price_type']))return $this->error(['desc'=>'错误的计价方式！']);
            if(!isset($v['fact_price']))return $this->error(['desc'=>'错误的单价！']);
            if(!isset($v['total_price']))return $this->error(['desc'=>'错误的价格！']);
            if(!isset($v['num']))return $this->error(['desc'=>'错误的数量！']);
            if(isset($v['time_type'])&& (102 != $v['price_type'])){
                switch ($v['time_type']){
                    //时间模式的类型 0无限制 101小时段制 102日期段制 103日期制
                    case '101':
                        if(!isset($v['s_time']))return $this->error(['desc'=>'错误的开始时间！']);
                        if(!isset($v['e_time']))return $this->error(['desc'=>'错误的结束时间！']);
                        break;
                    case '102':
                        if(!isset($v['s_date']))return $this->error(['desc'=>'错误的开始日期！']);
                        if(!isset($v['e_date']))return $this->error(['desc'=>'错误的结束日期！']);
                        break;
                    case '103':
                        if(!isset($v['u_date']))return $this->error(['desc'=>'错误的使用日期！']);
                        break;
                    default:
                }
            }
            if(!isset($v['back_points']))return $this->error(['desc'=>'错误的积分！']);
        }

        $order_data = array_map(function($d)use($order_id){
            return [
                'order_id'      => $order_id,
                'services_id'   => isset($d['services_id'])?$d['services_id']:'0',
                'plan_id'       => isset($d['id'])?$d['id']:'0',
                'item_type'     => $d['plan_type'],
                'price_type'    => $d['price_type'],
                'unit_price'    => $d['fact_price'],
                'total_price'   => $d['total_price'],
                'num'           => $d['num'],
                'time_type'     => isset($d['time_type'])?$d['time_type']:'0',
                's_time'        => isset($d['s_time'])?$d['s_time']:'0',
                'e_time'        => isset($d['e_time'])?$d['e_time']:'0',
                's_date'        => isset($d['s_date'])?$d['s_date']:'0',
                'e_date'        => isset($d['e_date'])?$d['e_date']:'0',
                'u_date'        => isset($d['u_date'])?$d['u_date']:'0',
                'title'         => isset($d['title'])?$d['title']:'',
                'back_points'   => $d['back_points'],
                'c_time'        => floor(THINK_START_TIME),
                'del_time'      => '0',
                'comment_time'  => '0',
                'book_status'   => (101 == $d['need_book'])?'101':'0',
                'ftof'          => isset($d['ftof'])?$d['ftof']:'102',
                'verify'        => isset($d['verify'])?$d['verify']:'102',
            ];
        },$data);
        $model = model('OrderItem');
        if($model->saveAll($order_data)){
            return $this->success($model);
        }else{
            return $this->error(['desc'=>'创建订单项失败，请稍后再试！']);
        }
    }

    //完成订单 $data 是订单数据
    public function complete($data){
        //todo 这里更合理是使用多态，在对应的类中去处理后续逻辑
        //100扫码消费 101购买服务/商品 102参加活动 103蹭车
        switch ($data['order_type']){
            case '100':
                //完成扫码支付
                // 不需要转账，这里商家的逻辑是走对账单
                model('msg','service')
                    ->type(101, 106)
                    ->sender($data['seller_id'],$data['seller_type'])
                    ->target( $data['user_id'], 101)
                    ->action(false, $data['id'])
                    ->content('扫码消费：'.number_format($data['total_price'],2).'元', $data['title'])
                    ->send()
                    ->push();
                ////                if(model('money','service')->add($data['total_price'],$data['seller_id'],$data['seller_type'])){
//                    //扫码完成，
//
//                    //1.给用户
//                    model('msg','service')
//                        ->type(101, 106)
//                        ->sender($data['seller_id'],$data['seller_type'])
//                        ->target( $data['user_id'], 101)
//                        ->action(false, $data['id'])
//                        ->content('扫码消费：'.number_format($data['total_price'],2).'元', $data['title'])
//                        ->send()
//                        ->push();
//
//                    // todo 应该给2个通知
//                    //2.给商家
//
//
//                    return $this->success([]);
//                }else{
//                    return $this->error([],'加钱失败',201);
//                }
                break;
            case '101':
                //购买商品和服务，1.给商家加钱，2.给出通知，3.减去库存
//                if(model('money','service')->add($data['total_price'],$data['seller_id'],$data['seller_type'])){
                    $content = implode('、',array_map(function($i){
                        return $i['title'];
                    },$data['order_item']));

                    //发通知给用户
                    model('msg','service')
                        ->type(101, 106)
                        ->sender($data['seller_id'],$data['seller_type'])
                        ->target( $data['user_id'], 101)
                        ->action(false, $data['id'])
                        ->content('您的'.$content.'已经预定成功！', $data['title'])
                        ->send()
                        ->push();

                    // todo 短信给商家
                    if((102 == $data['seller_type']) || (103 == $data['seller_type'])) {
                        //101user用户 102place地点 103seller商家用户
                        $seller = model('seller')->with(['place']);
                        if(103 == $data['seller_type']){
                            $seller->where('id',$data['seller_id']);
                        }elseif (102 == $data['seller_type']){
                            $seller->where('place_id',$data['seller_id']);
                        }

                        $seller = $seller->find();

                        if($seller && $seller['place']){
                            if('101' == $seller['place']['notice']){
                                $phone = $seller['place']['phone'];
                                $pre   = $seller['place']['pre'];
                                if($phone){
                                    $sms = new \transfer\Sms();
                                    $sms->send([],201,$phone ,$pre?$pre:null);
                                }
                            }
                        }
                    }

                    //减去库存
                    if(isset($data['order_item']) && $data['order_item']){
                         foreach ($data['order_item'] as $vo){
                             model('plan')->where('id',$vo['plan_id'])->setDec('stock',$vo['num']);
                         }
                    }
                    return $this->success([]);
//                }else{
//                    return $this->error([],'加钱失败',201);
//                }
                break;
            case '102':
                //参加活动 1.设置参加表状态为正常 2.给发起者发出活动参加请求 3.
                $join = model('artJoin')->where('order_id',$data['id'])->where('status',103)->find();
                if($join){
                    $join->status = 102;
                    $join->save();
                }else{
                    return $this->error(['desc'=>'没找到参加记录或记录状态错误！']);
                }

                $content = implode('、',array_map(function($i){
                    return $i['title'];
                },$data['order_item']));

                //发通知给发起者
                $msg = model('msg','service')
                    ->type(102, 103)
                    ->sender( $data['user_id'],101);
                    if(101 == $data['seller_type']){
                        $msg->target( $data['seller_id'], 101);
                    }else if(102 == $data['seller_type']){
                        $msg->target( $data['seller_id'], 110);
                    }else if(103 == $data['seller_type']){
                        $msg->target( $data['seller_id'], 111);
                    }else{
                        //错误的接收方
                        return $this->error(['desc'=>'消息接收方错误！']);
                    }
                    $msg->action(101, $join['id'],102)
                    ->content('已选'.$content.'套餐','申请参加您发起的活动'.$data['title'])
                    ->send()
                    ->push();
                return $this->success([]);
                break;
            case '103':
                //蹭车 1.设置蹭车状态正常 2.给被蹭车的人发出蹭车请求
                $art_rub_car = model('artRubCar')->where('order_id',$data['id'])->where('status',103)->find();
                if($art_rub_car){
                    $art_rub_car->status = 102;
                    $art_rub_car->save();
                }else{
                    return $this->error(['desc'=>'没找到蹭车记录或记录状态错误！']);
                }

                $addi = '';
                if($data['total_price'])$addi .= '+ 已预付 '.number_format($data['total_price']/100,2).'￥';
                if($data['total_cons_points'])$addi .= '+ '.$data['total_cons_points'].'积分';
                if($addi) $addi .= ' 作为乘车报酬';

                //发消息给被蹭车人
                $msg = model('msg','service')
                    ->type(102, 102)
                    ->sender( $data['user_id'],101);
                    if(101 == $data['seller_type']){
                        $msg->target( $data['seller_id'], 101);
                    }else if(102 == $data['seller_type']){
                        $msg->target( $data['seller_id'], 110);
                    }else if(103 == $data['seller_type']){
                        $msg->target( $data['seller_id'], 111);
                    }else{
                        //错误的接收方
                        return $this->error(['desc'=>'消息接收方错误！']);
                    }
                    $msg->action(101, $art_rub_car['id'])
                    ->content($art_rub_car['content'],'申请乘您的车')
                    ->addi($addi)
                    ->send()
                    ->push($art_rub_car['content'],'申请乘您的车');

                return $this->success([]);
                break;
            default:
                return $this->error(['desc'=>'错误的订单类型！']);
        }
    }

    /***********************************************************/
    //根据商家id创建商家对应的订单号前缀
    protected function createOrderNoPre($seller_id = 0,$seller_type = '101'){
        //查询今天该商家售出了多少订单
        if(100000 > $seller_id)$seller_id = str_pad($seller_id,6,'0',STR_PAD_LEFT);
        switch ($seller_type){
            //101user用户 102place地点 103seller商家用户
            case '101':
                $seller_type_str = 'U';
                break;
            case '102':
                $seller_type_str = 'P';
                break;
            case '103':
                $seller_type_str = 'M';
                break;
            default:
                $seller_type_str = 'N';
        }
        return $seller_type_str.$seller_id.'D'.date('Ymd').'N';
    }

    //传入套餐和选择参数，返回新的套餐数组
    protected function mergePlanData($in, $plan){
        $plan_arr = [];
        foreach ($plan as $k => $v){
            if(102 == $v['plan_type']){
                $is_mast = true;
            }else{
                $is_mast = false;
            }
            foreach ($in as $key => $val){
                if($val['id'] == $v['id']){
                    $is_mast = false;
                    $temp = $this->checkPlanData(array_merge($val,is_object($v)?$v->toArray():$v));
                    if($this->isError($temp)){
                        return $this->error($temp);
                    }else{
                        $plan_arr[] = $temp['data'];
                    }
                    break;
                }
            }
            //跑到这里，说明该套餐没有被选
            if($is_mast){
                return $this->error(['desc'=>'您需要同时选择'.$v['title']]);
            }
        }
        return $this->success($plan_arr);
    }

    //参数检查
    protected function checkPlanData($data = []){
        $start_time = strtotime(date('Y-m-d'));
        //数量判断
        if($data['num'] <= 0){
            return $this->error(['desc'=>'请至少选择1个'.$data['title']]);
        }
        if(isset($data['pre_num']) && $data['pre_num'] && ($data['pre_num'] < $data['num'])){
            return $this->error(['desc'=>$data['title'].' 限购'.$data['pre_num'].'个']);
        }
        if(isset($data['has_max_num']) && (101 == $data['has_max_num']) && ($data['stock'] < $data['num'])){
            return $this->error(['desc'=>$data['title'].' 库存不足！']);
        }

        if((101 == $data['price_type']) && $data['time_type']){
            //时间模式的类型 0无限制 101小时段制 102日期段制 103日期制
            if(101 == $data['time_type']){
                //计价，每小时
                if(!isset($data['s_time'])){
                    return $this->error(['desc'=>'请选择开始时间']);
                }elseif($data['s_time'] < ($start_time)){
                    return $this->error(['desc'=>'开始时间不能早于今天']);
                }
                if(!isset($data['e_time'])){
                    return $this->error(['desc'=>'请选择结束时间']);
                }elseif($data['e_time'] < ($start_time)){
                    return $this->error(['desc'=>'结束时间不能早于今天']);
                }
                $time = ($data['e_time'] - $data['s_time'])/3600;
            }elseif (102 == $data['time_type']){
                //计价，每天
                if(!isset($data['s_date'])){
                    return $this->error(['desc'=>'请选择开始日期']);
                }elseif($data['s_date'] < ($start_time)){
                    return $this->error(['desc'=>'开始日期不能早于今天']);
                }
                if(!isset($data['e_date'])){
                    return $this->error(['desc'=>'请选择结束日期']);
                }elseif($data['e_date'] < ($start_time)){
                    return $this->error(['desc'=>'结束日期不能早于今天']);
                }
                $time = ($data['e_date'] - $data['s_date'])/86400;
            }else{
                if(!isset($data['u_date'])){
                    return $this->error(['desc'=>'请选择使用日期']);
                }elseif($data['u_date'] < ($start_time)){
                    return $this->error(['desc'=>'使用日期不能早于今天']);
                }
                $time = 1;
            }
            if(0 >= $time){
                return $this->error(['desc'=>'选择的时间过短']);
            }
        }else{
            $time = 1;
        }

        //计算总价和积分
        $data['total_price'] = $time*$data['fact_price']*$data['num'];
        $data['total_points'] = $time*$data['back_points']*$data['num'];

        $data['book_status'] = (101 == $data['need_book'])?101:0;
        return $this->success($data);
    }

    //判断是否有支持面对面支付和验票的项目
    protected function has_ftof($array){
        //如果有其中一个是101就返回101
        if((array_sum($array)/102 == count($array))){
           return 102;
        }else{
            return 101;
        }
    }

    //获取一个套餐对象
    protected function planObjectData($data){
        $objetc = [
            'id'            => '0',
            'art_id'        => '0',
            'services_id'   => '0',
            'item_type'     => '101',
            'price_type'    => '102',
            'fact_price'    => '1',
            'back_points'   => '0',
            'use_points'    => '0',
            'num'           => '0',
            'title'         => '',
            'mast_buy'      => '102',
            'verify'        => '102',
            'ftof'          => '102',
            'need_book'     => '102',
            'plan_type'     => '100',
        ];
        return array_merge($objetc,$data);
    }

    //生成有效期内唯一的$verify_code
    protected function getVerifyCode($id,$type,$length = 8,$depth = 10){
        if($depth < 0){
            return false;
        }
        $min = pow(10,$length);
        $max = pow(10,$length+1)-1;
        $code = mt_rand($min,$max);
        if(model('order')->where('verify_code',$code)->where(function ($query){
            return $query->where('verify_expire','0')->whereOr('verify_expire','>',floor(THINK_START_TIME));
        })->where('seller_id',$id)->where('seller_type',$type)->find()){
            return $this->getVerifyCode($id,$type,$length,--$depth);
        }else{
            return $code;
        }
    }
}