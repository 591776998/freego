<?php
namespace app\index\controller;
use think\image\Exception;
use think\Request;
class Image extends \app\common\controller\Base
{
    protected $share_default_img = 'static/img/base/zhanwei.png';
    protected $thumbnail         = 'thumbnail';

    public function index(){



    }

    //获取图片
    public function read(){
        @header("Content-Type:image/jpeg");
        $img_id = $this->param('id',0);
        $width  = $this->param('width',80);
        $height = $this->param('height',80);
        $img_path = config('img_path');

        //查找该图片
        $path = '';
        if($img_id){
            $img = model('image')->where('id',$img_id)->find();
            if($img){
                $path = $img->getData('url');
            }
        }
        if(!$path || !isset($img) || !$img || !is_file('.'.$img_path.$path)){
            //赋值1张默认图片
            $path = $this->share_default_img;
        }
        if(false !== strpos($path,'/')){
            $ds = '/';
        }else{
            $ds = '\\';
        }
        $path_arr  = explode($ds,$path);
        $file_name = array_pop($path_arr);
        $file_name = $this->thumbnail.'_'.$file_name;
        array_push($path_arr,$file_name);
        $new_path  = '.'.$img_path.implode($ds,$path_arr);
        if(!is_file($new_path)){
            try{
                $image = \think\Image::open('.'.$img_path.$path);
                $image->thumb($width,$height)->save($new_path);
                return response( file_get_contents($new_path))->contentType("image/jpg");
            }catch (\Think\Exception $e){
                //返回默认图片
                return response(file_get_contents('.'.$img_path.$this->share_default_img))->contentType("image/jpg");
            }
        }else{
            return response(file_get_contents($new_path))->contentType("image/jpg");
        }
    }

    //新建图片，支持批量和单个
    public function save(){
        $file = $this->request->file();
        //图片类型 0未分类  101游记 102活动 103提问 104动态 105头像 106用户主页图片 107地点封面图 108服务封面图 109套餐封面图
        if(!$this->check('image_type',['number','r','min:0'])){
            return error($this->checkError());
        }
        if($file){
            //上传头像，并修改
            $img_result = model('image','service')->upload($file['img'],$this->param('image_type','0'));
            return success($img_result);
        }else{
            return error(['desc'=>'请选择您要上传的图片']);
        }
    }

    //递归生成文件目录
    protected function creatDir($full_path){
        $p = explode('/',$full_path);

    }

}