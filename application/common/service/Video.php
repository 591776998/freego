<?php
namespace app\common\service;

class Video extends \app\common\service\Base
{
    //1.多文件上传,返回文件id和url，失败的返回失败信息
    public function upload( $file, $type, $img_id){
        if( empty( $file)){
            $result = [];
        }else if( is_array( $file)){
            $result = [];
            foreach ( $file as $k => $f){
//                if(!$f->validate([ 'type' => 'image/jpeg,image/gif,image/png,image/jpg', 'ext' => 'mp4,jpg,png'])){
//                    return [];
//                }
                if(!is_numeric($k)){
                    $result[$k] = $this->upfile( $f, $type, $img_id);
                }else{
                    $result[$k] = $this->upfile( $f, $type, $img_id);
                }
            }
        }else{

//            if(!$file->validate([ 'type' => 'image/jpeg,image/gif,image/png,image/jpg', 'ext' => 'jpeg,jpg,png'])){
//                return [];
//            }
            $result = $this->upfile( $file, $type, $img_id);
        }
        return $result;
    }

    //批量获取文件对象
    public function getUrls( $id_arr){
        if(!$id_arr){
            return array();
        }
        $data = \app\common\model\Image::all($id_arr);
        if($data){
            return $data;
        }
        return array();
    }

    //获取单个文件对象
    public function getUrl( $id){
        if(!$id){
            return (object)null;
        }
        $data = \app\common\model\Image::get($id);
        if($data){
            return $data;
        }
        return (object)null;
    }


    //本地文件上传
    protected function upfile( $file,$type = 0, $img_id = 0){
        //使用逻辑层上传图片
        $upload = new Upload();
        return $upload->local( $file,$type,102,$img_id);
    }


}