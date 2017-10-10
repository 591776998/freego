<?php
namespace app\seller\controller;
class Placealbum extends \app\common\controller\SellerBase
{

    //新建
    public function save(){
        $data = $this->param;
        $model = model('placeAlbum');
        if($model->data($data)->save()){
            return success($model);
        }else{
            return error(['desc'=>'保存失败']);
        }
    }

    public function delete(){
        $record = model('placeAlbum')->where('place_id',$this->param('place_id'))->where('img_id',$this->param('img_id'))->find();
        if($record){
            if($record->destroy($record->id)){
                return $this->success($record);
            }else{
                return error(['desc'=>'删除失败']);
            }
        }else{
            return error(['desc'=>'未找到该记录']);
        }

    }
}