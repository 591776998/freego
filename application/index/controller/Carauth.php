<?php
namespace app\index\controller;
use think\Request;
class Carauth extends \app\common\controller\Base
{

    //添加1个未认证的车
    public function save(){
        if(!$this->user){
            return error(['desc'=> '请先登录,然后上传车型！'],'请先登录,然后上传车型！','401');
        }
        if(!$this->param('car_model_id',['number','r','min:0'])){
            return error($this->checkError());
        }

        //1.检查该用户的该排序的车是否存在
        $is_has = model('CarAuth')->where('user_id',$this->user['id'])->where('order_weight',$this->param('order_weight','0'))->find();
        if($is_has){
            if(101 == $is_has['is_check']){
                return error(['desc'=>"该车已经认证，请勿重复提交！"]);
            }elseif(103 == $is_has['is_check']){
                return error(['desc'=>"该车正在认证中，请勿重复提交！"]);
            }else{
                return error(['desc'=>"该车已经存在，请勿重复提交！"]);
            }
        }
        if(!in_array($this->param('order_weight','0'),[0,1,2])){
            return error(['desc'=>"最多只能提交3辆车！"]);
        }

        //获取品牌
        $model_sql = model('carModel')->field('car_type_id')->where('id',$this->param('car_model_id','0'))->select(false);
        $brand_sql = model('carType')->field('car_brand_id')->where('id','exp','=('.$model_sql.')')->select(false);
        $car_brand_res = model('carBrand')->field('title')->where('id','exp','=('.$brand_sql.')')->find();
        if($car_brand_res){
            $car_brand = $car_brand_res['title'];
        }else{
            $car_brand = '';
        }

        $save_data = [
            'user_id'       => $this->user['id'],
            'car_name'      => $this->param('car_name',''),
            'car_brand'     => $car_brand,
            'car_model_id'  => $this->param('car_model_id','0'),
            'order_weight'  => $this->param('order_weight',0),
            'car_full_name' => $this->param('car_full_name',''),
            'is_check'      => '102'
        ];
        $model = model('carAuth')->data($save_data);
        if($model->save()){
            $id = $model->id;
            $out = model('CarAuth')->where('id',$id)->with('carmodel')->find();
            return success($out);
        }else{
            return error(['desc'=>'保存失败，请稍后再试！']);
        }
    }

    //修改未认证的车的信息
    public function update(){
        if(!$this->check('id',['number','r','min:0'])){
            return error($this->checkError());
        }

        $record = model('carAuth')->where('id',$this->param('id','0'))->find();
        if(!$record){
            return error(['desc'=>"该车可能不存在，请稍后再试！"]);
        }
        if(102 != $record['is_check']){
            return error(['desc'=>"只能修改未认证的车的信息！"]);
        }

        //获取品牌
        if($this->param('car_model_id','0')){
            $model_sql = model('carModel')->field('car_type_id')->where('id',$this->param('car_model_id','0'))->select(false);
            $brand_sql = model('carType')->field('car_brand_id')->where('id','exp','=('.$model_sql.')')->select(false);
            $car_brand_res = model('carBrand')->field('title')->where('id','exp','=('.$brand_sql.')')->find();
            if($car_brand_res){
                $car_brand = $car_brand_res['title'];
            }
        }

        $save_data = [];
        if($this->has('car_model_id'))$save_data['car_model_id'] = $this->param['car_model_id'];
        if($this->has('car_name'))$save_data['car_name'] = $this->param['car_name'];
        if($this->has('car_full_name'))$save_data['car_full_name'] = $this->param['car_full_name'];
        if(isset($car_brand) && $car_brand)$save_data['car_brand'] = $car_brand;
        if(!$save_data){
            return error(['desc'=>'没有修改任何数据！']);
        }
        $m = model('CarAuth')->where('id',$this->param('id','0'))->data($save_data);
        if($m->update()){
            $out = model('CarAuth')->where('id',$this->param('id'))->with('carmodel')->find();
            return success($out);
        }else{
            return error(['desc'=>'修改失败！']);
        }
    }

    //删除一个车辆
    public function delete(){
        if(!$this->check('id',['number','r','min:0'])){
            return error($this->checkError());
        }

        $data = model('carAuth')->where('id',$this->param('id','0'))->find();
        if($data){
            if($data->destroy($this->param('id','0'))){
                return success((object)null);
            }else{
                return error(['desc'=>'删除失败！']);
            }
        }else{
            return error(['desc'=>'没找到该车型！']);
        }
    }
}