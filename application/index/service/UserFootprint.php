<?php
namespace app\index\service;
class UserFootprint extends \app\common\service\Base
{

    //增量的添加一个去过的地点
    public function add($user_id,$data){
        $data['user_id']    = $user_id;
        $data['c_time']     = floor(THINK_START_TIME);
        if(isset($data['location_name']) && isset($data['location_value'])){
            $record = model('user_footprint')
                ->where('user_id',$user_id)
                ->where('location_name',$data['location_name'])
                ->where('location_value',$data['location_value']);
                if(isset($data['place_id'])){
                    $record->where('place_id',(isset($data['place_id'])?$data['place_id']:'0'));
                }
            $record = $record->find();
            if(!$record){
                model('user_footprint')->allowField(true)->isUpdate(false)->data($data)->save();
            }
        }
        return true;
    }

    public function adds($user_id,$arr = []){
        if(!$arr){
            return true;
        }
        foreach ($arr as $k => $v){
            $this->add($user_id,$v);
        }
        return true;
    }


}