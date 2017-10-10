<?php
namespace app\index\controller;
class Order extends \app\common\controller\Base
{
    //post todo
    public function save(){
        if(!$this->user){
            return error(['desc'=> '请先登录！'],'请先登录！','401');
        }

        //参数检查
        if(!$this->check('type',['number','r','min:0'])){
            return error($this->checkError());
        }

        if(100 == $this->param('type')){
            if(!$this->check('seller_id',['number','r','min:1']) || !$this->check('money',['number','r','min:0']) ){
                return error($this->checkError());
            }
            if($this->param('money') <= 0){
                return error(['desc'=>'消费金额不能少于0元！']);
            }
        }else if(101 == $this->param('type')){
            if(!$this->check('services_id',['number','r','min:1'])){
                return error($this->checkError());
            }
        }else if(102 == $this->param('type')){
            if(!$this->check('art_id',['number','r','min:1'])){
                return error($this->checkError());
            }
        }else if(103 == $this->param('type')){
            if(
                !$this->check('art_id',['number','r','min:1'])||
                !$this->check('money',['number','r','min:0'])||
                !$this->check('points',['number','r','min:0'])||
                !$this->check('target_id',['number','r','min:0'])
            ){
                return error($this->checkError());
            }
        }

        $main_data = $this->param;
        if(isset($main_data['plan']))unset($main_data['plan']);

        $res = model('Order','service')->save( $this->user,$this->param('type','0'),$main_data,$this->param('plan',[]));
        if($this->isError($res)){
            return error($res['data'],$res['desc'],$res['code']);
        }
        return success($res['data']);
    }

    //订单列表
    public function index(){
        if(!$this->user){
            return success([]);
        }

        if(!$this->check('type',['number','r','min:0'])){
            return success([]);
        }

        return success(model('Order','service')->index( $this->user,$this->param('type','0'),$this->param('page',1),$this->param('page_size',10)));
    }

    //订单详情
    public function read(){
        if(!$this->user){
            return error(['desc'=>'请登录后查看！']);
        }

        $res = model('Order','service')->read($this->user,$this->param('id',0));
        if(!$res){
            return error(['desc'=>'没找到该订单！']);
        }
        return success($res);
    }

    //取消订单
    public function delete(){
        if(!$this->user){
            return error(['desc'=>'请登录后取消！']);
        }

        $res = model('Order')->where('id',$this->param('id',0))->find();
        if(!$res){
            return error(['desc'=>'该订单已被删除或不存在！']);
        }else{
            if(0 != $res->pay_time){
                return error(['desc'=>"该订单已经支付，不能删除"]);
            }
            if($this->user['id'] != $res['user_id']){
                return error(['desc'=>"您不能删除该订单"]);
            }
            if($res->where('id',$res->id)->update(['status'=>98])){
                return success($res);
            }else{
                return error(['desc'=>'删除失败']);
            }
        }
    }
}