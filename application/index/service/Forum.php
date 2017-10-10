<?php
namespace app\index\service;
class Forum extends \app\common\service\Base
{

    //列表
    public function index($page = 1, $page_size = 10){
        $model = model('Forum');
        if($page && $page_size){
            $model->page($page,$page_size);
        }
        $model->order('order_weight desc')->order('id desc');
        return $model->select();
    }

    //虚构一个全球总会
    public function read($id){
        if(0 == $id){
            return [
                'id'            => '0',
                'title'         => '全球总会',
                'del_time'      => '0',
                'c_time'        => '1503678630',
                'order_weight'  => '100',
                'status'        => '101',
                'p_id'          => '0',
                'is_forum'      => '101',
                'location_name' => '',
                'location_value'=> '',
                't_code'        => '',
                'c_code'        => '',
                'p_code'        => '',
                'city'          => '',
                's_code'        => ''
            ];
        }else{
            return model('forum')->where('id',$id)->find();
        }
    }

    //根据地区返回论坛
    public function getForumByLocation($name = '', $value = ''){
        if($name) {
            return model('forum')->where('is_forum', 101)->where(function ($query) use ($name, $value) {
                $query->where(function ($query) use ($name, $value) {
                    $query->where('location_name', $name)->where('location_value', $value);
                })->whereOr(function ($query) use ($name, $value) {
                    if ($name) {
                        $query->where($name, $value);
                    }
                });

            })->find();
        }else{
            return $this->read(0);
        }
    }
}
