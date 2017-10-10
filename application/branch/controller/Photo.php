<?php
namespace app\branch\controller;
class Photo extends \app\common\controller\BranchBase
{
    public function index(){
        if(!$this->branch)return $this->errorLogin();

        $location_name = $this->branch['location_name'];
        $location_value = $this->branch['location_value'];

        //查询出属于该论坛的精彩影像
        $wonderful_img = model('wonderfulImg')->with(['img','user']);
        $data = $wonderful_img->where(function ($query)use($location_name,$location_value){
            return $query->where($location_name,$location_value)->whereOr(function ($query)use($location_name,$location_value){
                $query->where('location_name',$location_name)->where('location_value',$location_value);
            });
        })->page($this->param('page',1),$this->param('page_size',10))->order('id desc')->select();

        return success($data);
    }

    public function update(){
        if(!$this->branch)return $this->errorLogin();
        if(!$this->check('id',['number','r','min:0'])){
            return error($this->checkError());
        }
        $record = model('wonderfulImg')->where('id',$this->param('id'))->find();
        if(!$record){
            return error(['desc'=>'未找到该图片']);
        }
        $record->status = $this->param('status',101);
        if($record->save()){
            return success($record);
        }else{
            return error(['desc'=>'没有做任何修改']);
        }
    }

    public function delete(){
        if(!$this->branch)return $this->errorLogin();
        if(!$this->check('id',['number','r','min:0'])){
            return error($this->checkError());
        }
        $record = model('wonderfulImg')->where('id',$this->param('id'))->find();
        if(!$record){
            return error(['desc'=>'未找到该图片']);
        }
        if($record->destroy($record->id)){
            return success($record);
        }else{
            return error(['desc'=>'删除失败']);
        }
    }
}