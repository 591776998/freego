<?php
namespace app\branch\controller;
class Wallet extends \app\common\controller\BranchBase
{

    public function index(){


    }

    //服务器请求月份数据
    public function m(){
        $url = 'http://fr.yoyopeilian.com/index/org/info';
        $data = $this->param;
        $res = http_curl($url,[],$data);
        $res = json_decode($res,true);
        if($res){
            if(200 != $res['code']){
                return error($res['desc']);
            }else{
                return success($res['data']);
            }
        }else{
            return error(['desc'=>'获取数据失败']);
        }
    }

    public function month(){

        $this->assign('branch',$this->branch);
        return $this->fetch();
    }

    public function total(){
        //判断登录
        if(!$this->branch)return $this->errorLogin();
        $branch_id = $this->branch['id'];

        $today_start = strtotime(date('Y-m-d'));
        $yesterday_start = $today_start - 86400;
        $now_time = floor(THINK_START_TIME);


        //查询下级代理商们
        $res = [
            'total' => [
                'money'                 => '0',
                'user_count'            => '0',
                'seller_count'          => '0',
                'indirect_user_count'   => '0',
                'indirect_seller_count' => '0',
            ],
            'today' => [
                'money'                 => '0',
                'user_count'            => '0',
                'seller_count'          => '0',
                'indirect_user_count'   => '0',
                'indirect_seller_count' => '0',
                'user_count_diff'       => '0',
            ]
        ];
        return success($res);
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