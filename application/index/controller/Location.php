<?php
namespace app\index\controller;
use think\Request;
class Location extends \app\common\controller\Base
{

    public function save(){

    }

    //更新位置
    public function update(){
        if(!$this->user ){
            return error(['desc'=>'请登录再进行定位！'],'请登录再进行定位！');
        }
        $id = $this->param('id',0);
        $model = model('UserLocation');

        $save_data = [
            'lat'       => $this->param('lat'),
            'lng'       => $this->param('lng'),
            'c_time'    => floor(THINK_START_TIME),
            'sex'       => $this->user['sex']
        ];

        if($model->where('id',$id)->update($save_data)){
            return success($model->where('id',$id)->find());
        }else{
            //判断是不是不存在该用户的定位表，不存在就新建
            if(!$model->where('id',$id)->find()){
                $save_data['id'] = $id;
                if($model->save($save_data)){
                    return success($model->where('id',$id)->find());
                }
            }
        }
        return error(['desc'=>'保存定位信息失败！']);
    }

}