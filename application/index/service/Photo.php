<?php
namespace app\index\service;
use think\Db;
class Photo extends \app\common\service\Base
{

    public function index($user_id,$location_name,$location_value,$p,$ps){
        $data = model('WonderfulImg')->with(['art','img','user','like'=>function($query)use($user_id){
            return $query->where('user_id',$user_id);
        },]);
        if($location_name){
            $data->where(function ($query)use($location_name,$location_value){
                return $query->where($location_name,$location_value)->whereOr(function ($query)use($location_name,$location_value){
                    $query->where('location_name',$location_name)->where('location_value',$location_value);
                });
            });
        }
        $data->where('status',101)->page($p,$ps);
        $data = array_map(function($vo){
            if(!isset($vo['img_url']))$vo['img_url'] = '';
            if('' == $vo['location_str'])$vo['location_str'] = $this->getAddressFullName($vo['location_name'], $vo['location_value']);
            $vo['art_title'] = '';
            if($vo['art']){
                if(104 == $vo['art']['type']){
                    if($vo['user']){
                        $vo['art_title'] = $vo['user']['nick_name'].'的动态';
                    }else{
                        $vo['art_title'] = '动态';
                    }
                }else{
                    $vo['art_title'] = $vo['art']['title'];
                }
            }else{
                $vo['art_title'] = '文章';
            }
            return $vo;
        },$data->order('id desc')->select());
        return $this->success($data);
    }

    public function save($user_id,$save_data){
        //1保存图片
        unset($save_data['token']);
        $save_data['user_id']       = $user_id;
        if(!isset($save_data['location_str'])){
            $save_data['location_str']  = model('address','service')->getFullName($save_data['location_name'],$save_data['location_value']);
        }
        $save_data['status']        = 102;
        $save_data['c_time']        = floor(THINK_START_TIME);

        //获取上级全部location
        $location_arr = model('address','service')->getFather($save_data['location_name'],$save_data['location_value']);
        if(!isset($save_data[$save_data['location_name']]) ){
            if('c_code' == $save_data['location_name']){
                $save_data['c_code'] = $save_data['location_value'];
                if(isset($location_arr['father']['p_code'])){
                    $save_data['p_code'] = $location_arr['father']['p_code'];
                    if(isset($location_arr['father']['father']['s_code'])){
                        $save_data['s_code'] = $location_arr['father']['father']['s_code'];
                    }
                }
            }elseif ('city' == $save_data['location_name']){
                $save_data['city'] = $save_data['location_value'];
                if(isset($location_arr['father']['s_code'])){
                    $save_data['s_code'] = $location_arr['father']['s_code'];
                }
            }elseif ('t_code' == $save_data['location_name']){
                $save_data['t_code'] = $save_data['location_value'];
                if(isset($location_arr['father']['c_code'])){

                    $save_data['c_code'] = $location_arr['father']['c_code'];
                    if(isset($location_arr['father']['father']['p_code'])){

                        $save_data['p_code'] = $location_arr['father']['father']['p_code'];
                        if(isset($location_arr['father']['father']['father']['s_code'])){

                            $save_data['s_code'] = $location_arr['father']['father']['s_code'];
                        }
                    }
                }
            }
        }

        if(is_array($save_data['img_id'])) {
            $all = [];
            foreach (array_unique($save_data['img_id']) as $im){
                $temp = $save_data;
                $temp['img_id'] = $im;
                $all[] = $temp;
            }
            if($all){
                $res = model('WonderfulImg')->saveAll($all);
            }else{
                $res = false;
            }
        }else{
            $res = model('WonderfulImg')->data($save_data)->save();
        }
        if($res) {
            return success($res);
        }else{
            return error(['desc'=>'保存失败']);
        }
    }

}