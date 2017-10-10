<?php
namespace app\seller\controller;
class Wallet extends \app\common\controller\SellerBase
{
    public function month(){
        return $this->fetch();
    }

    public function index(){
        if(!$this->seller)return $this->errorLogin();
        $seller_id = $this->seller['id'];
        $place_id  = $this->seller['place_id'];

        $year_start = $this->param('year_time',strtotime(date('Y-01-01')));
        $m = $this->getMonth($year_start);
        $res = [];
        foreach ($m as $k => $v){
            $res[] = [
                'time_str'  => $v['str'].'月',
                'money'     => $this->sum($seller_id,$place_id,101,$v['s_time'],$v['e_time']),
                'point'     => $this->sum($seller_id,$place_id,102,$v['s_time'],$v['e_time']),
            ];
        }
        return success($res);
    }

    public function total(){
        //判断登录
        if(!$this->seller)return $this->errorLogin();
        $seller_id = $this->seller['id'];
        $place_id  = $this->seller['place_id'];

        $month_start = strtotime(date('Y-m-01'));
        $today_start = strtotime(date('Y-m-d'));
        $yesterday_start = $today_start - 86400;

        $now_time = floor(THINK_START_TIME);

        $today['money'] = $this->sum($seller_id,$place_id,101,$today_start,$now_time);
        $today['point'] = $this->sum($seller_id,$place_id,102,$today_start,$now_time);

        $yesterday['money'] = $this->sum($seller_id,$place_id,101,$yesterday_start,$today_start-1);
        $yesterday['point'] = $this->sum($seller_id,$place_id,102,$yesterday_start,$today_start-1);

        //1.现金总收入 101user用户 102place地点 103seller商家用户
        $res = [
            'total' => [
                'money'     => $this->sum($seller_id,$place_id,101,false,$now_time),
                'point'     => $this->sum($seller_id,$place_id,102,false,$now_time)
            ],
            'month' => [
                'money'     => $this->sum($seller_id,$place_id,101,$month_start,$now_time),
                'point'     => $this->sum($seller_id,$place_id,102,$month_start,$now_time)
            ],
            'today' => [
                'money'     => $today['money'],
                'point'     => $today['point'],
                'money_diff'=> $today['money']-$yesterday['money'],
                'point_diff'=> $today['point']-$yesterday['point'],
            ]
        ];
        return success($res);
    }

    //查询一段时间的订单情况
    protected function sum($seller_id,$place_id,$type,$start_time = false,$end_time = false){

        $total = model('order');
        if(101 == $type){
            $total->field('sum(`total_price`)-sum(`profit`) as res');
        }elseif(102 == $type){
            $total->field('sum(`total_cons_points`)-sum(`total_back_points`) as res');
        }
            if(false !== $start_time && false !== $end_time){
                $total->where('c_time','>=',$start_time)->where('c_time','<',$end_time);
            }elseif (false !== $start_time){
                $total->where('c_time','>=',$start_time);
            }elseif (false !== $end_time){
                $total->where('c_time','<',$end_time);
            }

        $res = $total->where(function ($query)use($seller_id,$place_id){
                        $query->where(function ($query)use($seller_id){
                            $query->where('seller_id',$seller_id)->where('seller_type',103);
                        })->whereOr(function ($query)use($place_id){
                            $query->where('seller_id',$place_id)->where('seller_type',102);
                        });
                    })->where('pay_time','<>','0')->where('refund_time','0')->find();
        if($res){
            return is_null($res['res'])?'0':$res['res'];
        }else{
            return '0';
        }
//
//
//
//        //收入
//        $total1 = model('follow');
//            if(false !== $start_time && false !== $end_time){
//                $total1->where('c_time','>=',$start_time)->where('c_time','<=',$end_time);
//            }elseif (false !== $start_time){
//                $total1->where('c_time','>=',$start_time);
//            }elseif (false !== $end_time){
//                $total1->where('c_time','<=',$end_time);
//            }
//
//        $total1 = $total1->where('type',$type)
//            ->where(function ($query)use($seller_id,$place_id){
//                $query->where(function ($query)use($seller_id){
//                        $query->where('receiver_id',$seller_id)->where('receiver_type',103);
//                    })->whereOr(function ($query)use($place_id){
//                        $query->where('receiver_id','in',$place_id)->where('receiver_type',102);
//                    });
//            })->sum('number');
//
//        //支出
//        $total2 = model('follow');
//            if(false !== $start_time && false !== $end_time){
//                $total2->where('c_time','>=',$start_time)->where('c_time','<=',$end_time);
//            }elseif (false !== $start_time){
//                $total2->where('c_time','>=',$start_time);
//            }elseif (false !== $end_time){
//                $total2->where('c_time','<=',$end_time);
//            }
//        $total2 = $total2->where('type',$type)
//            ->where(function ($query)use($seller_id,$place_id){
//                $query->where(function ($query)use($seller_id){
//                    $query->where('source_id',$seller_id)->where('source_type',103);
//                })->whereOr(function ($query)use($place_id){
//                    $query->where('source_id','in',$place_id)->where('source_type',102);
//                });
//            })->sum('number');
//        return $total1 - $total2;
    }

    //传入1年的开始时间戳，返回每月的开始时间戳
    protected function getMonth($y){
        $m = [1,2,3,4,5,6,7,8,9,10,11,12];
        $m_arr = [];
        $y_str = date('Y',$y);
        foreach ($m as $v){
            $s_time = strtotime($y_str.'-'.$v.'-1');
            if($s_time < THINK_START_TIME){
                if(12 != $v){
                    $e_time = strtotime($y_str.'-'.($v+1).'-1')-1;
                }else{
                    $e_time = strtotime(($y_str+1).'-'.($v+1).'-1')-1;
                }

                $m_arr[] = [
                    's_time'  => $s_time,
                    'e_time'  => $e_time,
                    'str'     => $v
                ];
            }
        }
        return $m_arr;
    }
}