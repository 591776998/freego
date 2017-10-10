<?php
namespace app\docking\controller;
class Seller extends \app\common\controller\DockingBase
{
    public function index(){
        $next_id = $this->param('next_id',0);
        $p = $this->param('page','');
        $ps = $this->param('page_size','');
        $data = model('seller');
        if($next_id){
            $data->where('id','>',$next_id);
        }
        if($p && $ps){
            $data->page($p,$ps);
        }
        if($this->param('order')){
            $data->order($this->param('order').' desc');
        }
        return success($data->select());
    }
}
