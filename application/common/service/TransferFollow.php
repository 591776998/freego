<?php
namespace app\common\service;

class TransferFollow extends \app\common\service\Base
{
    //转账人
    protected $from_id      = '0';
    protected $from_type    = '101';

    //收款人
    protected $to_id        = '0';
    protected $to_type      = '102';

    // 101 收入加支持记录 102支出记录 103收入记录
    protected $type         = '0';

    //支付/收款方式 0未知 100对账单支付 101微信支付 102支付宝 103零钱
    protected $pay_type     = '0';
    protected $get_type     = '0';

    //备注
    protected $content      = '';

    //转账人
    public function from($id,$type = false,$pay_type = false){
        $this->from_id = $id;
        if($type)$this->from_type = $type;
        if($type)$this->pay_type = $pay_type;
        return $this;
    }

    //收款人
    public function to($id,$type = false,$get_type = false){
        $this->to_id = $id;
        if($type)$this->to_type = $type;
        if($type)$this->get_type = $get_type;
        return $this;
    }

    //记录的类型
    public function type($type){
        $this->type = $type;
        return $this;
    }

    //备注
    public function content($content = ''){
        $this->content = $content;
        return $this;
    }

    //保存
    public function save(){
        return model('TransferFollow')->data([
            'content'       => $this->content,
            'type'          => $this->type,
            'from_id'       => $this->type,
            'from_type'     => $this->type,
            'pay_type'      => $this->type,
            'to_id'         => $this->type,
            'to_type'       => $this->type,
            'get_type'      => $this->type,
            'c_time'        => floor(THINK_START_TIME),
            'del_time'      => '0'
        ])->save();
    }
}