<?php
namespace app\main\controller;
class Address extends \app\common\controller\MainBase
{

    //地点编辑页面
    public function editor(){

        $data = model('addressCountry')->with('img')->select();

        $data = array_map(function($vo){
            if(!isset($vo['img_url']))$vo['img_url'] = '';
            return $vo;
        },$data);

        $this->assign('data',$data);
        return $this->fetch();
    }

    //获取下级城市
    public function sub(){
        $location_name = $this->param('location_name','');
        $location_value = $this->param('location_value','');
        if(!$location_name){
            return error(['desc'=>'获取下级城市失败']);
        }
        $model = new \app\index\service\Address();
        $data = $model->getSub($location_name,$location_value,1);
        return success($data);
    }

    //上传地点图片
    public function img(){
        $file = $this->request->file();

        $data = [];

        if($this->has('name'))$data['name'] = $this->param('name','');
        if($this->has('main_name'))$data['main_name'] = $this->param('main_name','');

        if($file && $file['img'])
        {
            $img_result = model('image','service')->upload($file['img'],$this->param('img_type',117));
            if($img_result){
                $data['img_id'] = $img_result['id'];
            }
        }else{
            if($this->param('img_id')){
                $data['img_id'] = $this->param('img_id',0);
            }
        }

        if(!$data){
            return error(['desc'=>'没有任何修改']);
        }

        $r = model('address')->getLocation($this->param('location_name',''),$this->param('location_value',''));
        if(!$r){
            return error(['desc'=>'没找到该地点']);
        }

        if($r->where('id',$r->id)->update($data)){
            return success($r);
        }else{
            return error(['desc'=>'上传失败']);
        }
    }
}