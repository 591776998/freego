<?php
namespace app\index\controller;
use think\Request;
class Video extends \app\common\controller\Base
{

    public function index(){



    }

    //新建文件，支持单个
    public function save(){
        $file = $this->request->file();
        //图片类型 0未分类 104动态
        if(!$this->check('video_type',['number','r','min:0'])){
            return error($this->checkError());
        }
        if($file){
            if(isset($file['img'])){
                $result = model('image','service')->upload($file['img'],$this->param('img_type','104'));
                $img_id = $result['id'];
            }elseif(isset($file['img_id'])){
                $img_id = $file['img_id'];
            }
            if(!isset($img_id)){
                $img_id = '0';//这里给一个默认图片Id
            }

            //上传视频
            $result = model('video','service')->upload($file['video'],$this->param('video_type','104'),$img_id);
            return success($result);
        }else{
            return error(['desc'=>'请选择您要上传的文件！']);
        }
    }
}