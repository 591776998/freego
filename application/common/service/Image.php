<?php
namespace app\common\service;

class Image extends \app\common\service\Base
{
    //1.多图片上传,返回图片id和url，失败的返回失败信息
    public function upload( $file, $type){
        if( empty( $file)){
            $result = [];
        }else if( is_array( $file)){
            $result = [];
            foreach ( $file as $k => $f){
                if(!$f->validate([ 'type' => 'image/jpeg,image/gif,image/png,image/jpg', 'ext' => 'jpeg,jpg,png,gif'])){
                    return [];
                }
                if(!is_numeric($k)){
                    $result[$k] = $this->upfile( $f, $type);
                }else{
                    $result[] = $this->upfile( $f, $type);
                }
            }
        }else{
            if(!$file->validate([ 'type' => 'image/jpeg,image/gif,image/png,image/jpg', 'ext' => 'jpeg,jpg,png,gif'])){
                return [];
            }
            $result = $this->upfile( $file, $type);
        }
        return $result;
    }

    //批量获取图片对象
    public function getUrls( $img_id_arr){
        if(!$img_id_arr){
            return array();
        }
        $data = \app\common\model\Image::all($img_id_arr);
        if($data){
            return $data;
        }
        return array();
    }

    //获取单个图片对象
    public function getUrl( $img_id){
        if(!$img_id){
            return (object)null;
        }
        $data = \app\common\model\Image::get($img_id);
        if($data){
            return $data;
        }
        return (object)null;
    }

    //传入一个全地址的url，返回图片id
    public function transfer($url = '', $type = 105){
        if(!$url){
            return [];
        }

        $img = new \app\common\model\Image([
            'type'      =>  $type,
            'url'       =>  $url,
            'status'    =>  '101',
            'c_time'    => THINK_START_TIME,
        ]);
        if($img->isUpdate(false)->save()) {
            return model('image')->where('id', $img->id)->find()->toArray();
        }else{
            return [];
        }
    }

    //本地图片上传
    protected function upfile( $file,$type = 0){
        //使用逻辑层上传图片
        $upload = new Upload();
        return $upload->local( $file,$type);
    }

}