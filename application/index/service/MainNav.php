<?php
namespace app\index\service;
use think\Db;
class MainNav extends \app\common\service\Base
{
    //传入上层的城市，保存主路线
    public function newMainArr($user_id,$main_arr){

        if(!$user_id){
            return [];
        }

        $data = array_values(array_map(function($m){
            return [
                'title'          => isset($m['city_title'])?$m['city_title']:(isset($m['title'])?$m['title']:''),
                'location_name'  => isset($m['c_code'])?'c_code':'',
                'location_value' => isset($m['c_code'])?$m['c_code']:''
            ];
        },$main_arr));

        $record = model('UserNav')->where('user_id',$user_id)->find();
        if($record){
            return $record->data([
                'user_id'   => $user_id,
                'data'      => json_encode($data),
                'm_time'    => floor(THINK_START_TIME),
            ])->save();
        }else{
            return model('UserNav')->isUpdate(false)->data([
                'user_id'   => $user_id,
                'data'      => json_encode($data),
                'm_time'    => floor(THINK_START_TIME),
                'del_time'  => '0'
            ])->save();
        }
    }

    //获取用户主路线
    public function read($user_id){
        if(!$user_id){
            return [];
        }
        $data = model('UserNav')->where('user_id',$user_id)->find();
        if(!$data){
            return [];
        }
        $data = $data['data'];
        $code_arr = [];
        foreach ($data as $v){
            if(!isset($code_arr[$v['location_name']])){
                $code_arr[$v['location_name']] = [];
            }
            array_push($code_arr[$v['location_name']],$v['location_value']);
        }
        $place_key_arr = ['t_code','c_code','p_code','s_code','city'];
        //查询这些地点下的用户收藏的地点
        $collect_sql_sub = model('collect')->field('target_id')->where('user_id',$user_id)->where('type',104)->select(false);
        $place_data = model('place')->where('id','exp',"in($collect_sql_sub)")->where(function ($query)use($code_arr,$place_key_arr){
            return $query->where(function ($query)use($code_arr,$place_key_arr){
                foreach ($code_arr as $k => $v){
                    if(in_array($k,$place_key_arr)){
                        foreach ($v as $item){
                            $query->whereOr($k,$item);
                        }
                    }
                }
                return $query;
            });
        })->select();
        $res = array_map(function($item)use($place_data){
            $item['sub'] = [];
            foreach ($place_data as $val) {
                //如果
                if (isset($val[$item['location_name']]) && ($val[$item['location_name']] == $item['location_value'])) {
                    array_push($item['sub'],$val);
                }
            }
            return $item;
        },$data);
        return $this->success($res);
    }
}
