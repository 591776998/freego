<?php
namespace app\main\controller;
class Map extends \app\common\controller\MainBase
{

    public function main(){
        if($this->has('line_id')){
            $data = model('lineItem')->where('line_id',$this->param('line_id',0))->select();
        }else{
            $data = [];
        }
        $this->assign('data',$data);
        return $this->fetch();
    }
}