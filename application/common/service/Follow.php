<?php
namespace app\common\service;
class Follow extends \app\common\service\Base
{
    protected $type         = '101';//101金额变动 102积分变动
    protected $sub_type     = '101';//99退款 100扫码 101购买商品 102参加活动 103蹭车
    protected $order_id     = '0';//相关订单id

    protected $source_id    = '0';//发起者的id
    protected $source_type  = '101';//100 系统 101普通用户 102地点 103商家 104代理商

    protected $receiver_id  = '0';//接收者id
    protected $receiver_type= '103';//100 系统 101普通用户 102地点 103商家 104代理商

    protected $s_content    = '';
    protected $r_content    = '';//

    protected $number       = '0';//金额或数量

    //金额和积分流水表，
    public function type($type = false,$sub_type = false,$order = 0){
        if(false !== $type)$this->type = $type;
        if(false !== $sub_type)$this->sub_type = $sub_type;
        if($order)$this->order_id = $order;
        return $this;
    }

    //发起者
    public function from($id = 0,$type = false,$content = ''){
        if($id)$this->source_id = $id;
        if(false !== $type)$this->source_type = $type;
        if($content)$this->s_content = $content;
        return $this;
    }

    //接收者
    public function to($id = 0,$type = false,$content = ''){
        if($id)$this->receiver_id = $id;
        if(false !== $type)$this->receiver_type = $type;
        if($content)$this->r_content = $content;
        return $this;
    }

    //内容
    public function content($s1 = '',$s2 = ''){
        if($s1)$this->s_content = $s1;
        if($s2)$this->r_content = $s2;
        return $this;
    }

    //金额 链式操作结束
    public function num($num = 0){
        if($num)$this->number = $num;
        //这里存库
        return $this->save();
    }

    protected function save(){
        $follow = model('follow');
        $follow->data([
            'order_id'      => $this->order_id,
            'type'          => $this->type,
            'sub_type'      => $this->sub_type,
            'number'        => $this->number,
            'source_id'     => $this->source_id,
            'source_type'   => $this->source_type,
            'receiver_id'   => $this->receiver_id,
            'receiver_type' => $this->receiver_type,
            's_content'     => $this->s_content,
            'r_content'     => $this->r_content,
            'c_time'        => floor(THINK_START_TIME),
            'del_time'      => '0'
        ]);
        if($follow->isUpdate(false)->save()){
            return true;
        }else{
            return false;
        }
    }


}