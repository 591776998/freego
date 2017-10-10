<?php
namespace app\common\service;
class Upload extends \app\common\service\Base
{
    protected $img_path   = ROOT_PATH . 'public' . DS . 'uploads';
    protected $video_path = ROOT_PATH . 'public' . DS . 'uploads/video';

    //上传
    public function local( $file, $type = 0, $file_type = 101, $img_id = 0){
        if($file){
            if(101 == $file_type){
                return $this->upImg($file, $type );
            }else{
                return $this->upVideo($file, $type, $img_id);
            }
        }else{
            //获取路径配置失败，不太可能出现
            return [
                'id'        => 0,
                'img_url'   => '',
                'img_id'    => '0',
                'width'     => '0',
                'height'    => '0',
                'url'       => '',
                'desc'      => '没获取到文件'
            ];
        }
    }

    //上传图片
    protected function upImg($file, $type = 0){
        $path = $this->img_path;
        if($path){

            $width = 0;
            $height = 0;

            if(110 == $type) {
                try {
                    $img_file = \think\Image::open($file);
                    $width = $img_file->width();
                    $height = $img_file->height();
                } catch (\Think\Exception $e) {
                }
            }

            $info = $file->move($path);
            if($info){
                $img = new \app\common\model\Image([
                    'type'      => $type,
                    'url'       => $info->getSaveName(),
                    'width'     => $width,
                    'height'    => $height,
                    'status'    => '101',
                    'c_time'    => THINK_START_TIME,
                ]);
                if($img->isUpdate(false)->save()){
                    return model('image')->where('id',$img->id)->find()->toArray();
                }else{
                    //入库失败
                    return [
                        'id'    => 0,
                        'url'   => '',
                        'width'     => '0',
                        'height'    => '0',
                        'desc'  => '文件保存失败'
                    ];
                }
            }else{
                return [
                    'id'    => 0,
                    'url'   => '',
                    'width'     => '0',
                    'height'    => '0',
                    'desc'  => '文件添加失败'
                ];
            }
        }else{
            //获取路径配置失败，不太可能出现
            return [
                'id'    => 0,
                'url'   => '',
                'width'     => '0',
                'height'    => '0',
                'desc'  => '文件上传失败'
            ];
        }
    }

    //上传视频
    protected function upVideo($file, $type = 0, $img_id = 0){
        $path = $this->video_path;
        if($path){
            $info = $file->move($path);
            if($info){
                $img = new \app\common\model\Video([
                    'img_id'    => $img_id,
                    'type'      => $type,
                    'url'       => $info->getSaveName(),
                    'status'    => '101',
                    'c_time'    => THINK_START_TIME,
                ]);
                if($img->isUpdate(false)->save()){
                    return model('video')->with(['img'])->where('id',$img->id)->find();
                }else{
                    //入库失败
                    return [
                        'id'    => 0,
                        'img_url'   => '',
                        'img_id'=> '0',
                        'url'   => '',
                        'desc'  => '文件保存失败'
                    ];
                }
            }else{
                return [
                    'id'    => 0,
                    'img_url'   => '',
                    'img_id'=> '0',
                    'url'   => '',
                    'desc'  => '文件添加失败'
                ];
            }
        }else{
            //获取路径配置失败，不太可能出现
            return [
                'id'    => 0,
                'img_url'   => '',
                'img_id'=> '0',
                'url'   => '',
                'desc'  => '文件上传失败'
            ];
        }
    }
}
