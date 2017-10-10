<?php
namespace app\docking\controller;
class Order extends \app\common\controller\DockingBase
{
    public function index(){
        $next_id = $this->param('next_id',0);
        $p = $this->param('page','');
        $ps = $this->param('page_size','');
        $data = model('order')->with(['seller_place']);
        $data->where('pay_time','<>','0')->where('refund_time',0)->where('close_time','<>',0);
        if($next_id){
            $data->where('id','>',$next_id);
        }
        if($p && $ps){
            $data->page($p,$ps);
        }
        if($this->param('order')){
            $data->order($this->param('order').' desc');
        }
        return success(array_map(function($o){
            $o = $o->toArray();

            switch ($o['seller_type'].''){
                case '101':
//                    $o['seller'] = $o['seller_user'];
                    break;
                case '102':
//                    $o['seller']      = isset($o['seller_place']['seller'])?$o['seller_place']['seller']:null;
                    $o['seller_type'] = '103';
                    $o['seller_id']   = isset($o['seller_place']['seller']['id'])?$o['seller_place']['seller']['id']:'0';
                    break;
                case '103':
//                    $o['seller'] = $o['seller_seller'];
                    break;
                default:
//                    $o['seller'] = null;
            }

            unset($o['seller_user']);
            unset($o['seller_place']);
            unset($o['seller_seller']);
            return $o;
        },$data->select()));
    }
}
