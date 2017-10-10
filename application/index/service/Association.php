<?php
namespace app\index\service;
class Association extends \app\common\service\Base
{

    //列表
    public function index( $p_id = 0){
        $data = model('Association')->with(['sub'=>function($query){
            return $query->with(['sub'])->order('order_weight','desc')->order('id desc');
        }]);
        $result = $data->where('p_id',$p_id)->order('order_weight desc')->order('id desc')->select();
        return $this->success($result);
    }

    //新建
    public function add( $p_id, $title, $order_weight = 100){
        $save_data = [
            'p_id'          => $p_id,
            'title'         => $title,
            'del_time'      => 0,
            'order_weight'  => $order_weight
        ];
        $model = model('Association');
        if($model->save($save_data)){
            $save_data['id'] = $model->id;
            return $this->success($save_data);
        }else{
            return $this->error(['desc'=>'新建协会失败']);
        }
    }

}